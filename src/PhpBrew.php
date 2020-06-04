<?php

namespace Zhwei\ValetPhpBrewExt;

use Valet\Brew;
use Valet\Configuration;
use function Valet\info;
use function Valet\output;
use function Valet\table;
use function Valet\warning;

class PhpBrew
{
    const PREFIX = '__phpbrew.';

    /**
     * @var string
     */
    protected $phpBrewRoot;

    protected $defaultPhpVersion;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var Brew
     */
    protected $brew;

    public function __construct(Configuration $config, Brew $brew)
    {
        $this->phpBrewRoot = $_SERVER['HOME'] . '/.phpbrew';
        if (isset($_SERVER['_']) && strpos($_SERVER['_'], $this->phpBrewRoot) === 0) {
            $suffix = substr($_SERVER['_'], strlen($this->phpBrewRoot) + 5);
            $version = explode('/', $suffix)[0];
            $this->defaultPhpVersion = $version;
        }

        $this->config = $config;
        $this->brew = $brew;
    }

    public function getSocketPath($version)
    {
        return $this->phpBrewRoot . "/php/php-{$version}/var/run/php-fpm.sock";
    }

    public function link($version, $name)
    {
        if (!file_exists(getcwd() . '/public/index.php')) {
            warning('Only support laravel/lumen for now');
            return;
        }

        $version = $version ?: $this->defaultPhpVersion;
        if (!$version) {
            warning("Invalid php version [{$version}]");
            $this->showAvailablePhpVersions();
            return;
        }

        if (strpos($version, 'php-') === 0) {
            $version = substr($version, 4);
        }

        $phpDir = $this->phpBrewRoot . "/php/php-{$version}";
        if (!file_exists($phpDir)) {
            warning("PHP version {$version} not found in {$phpDir}");
            $this->showAvailablePhpVersions();
            return;
        }

        $root = getcwd() . '/public';
        $name = $name ?: basename(getcwd());
        $domain = $name . '.' . $this->getTld();

        $socket = $this->getSocketPath($version);
        if (!file_exists($socket)) {
            warning("Socket file not found: {$socket}");
            warning('May be fpm service not start, try `phpbrew fpm start`');
            $this->showAvailablePhpVersions();
            return;
        }

        $replaces = [
            'DOMAIN' => $domain,
            'ROOT' => $root,
            'VALET_HOME_PATH' => VALET_HOME_PATH,
            'SOCKET' => $socket,
        ];
        $template = file_get_contents(__DIR__ . '/../stubs/site.conf');
        foreach ($replaces as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }
        $nginxConfigPath = VALET_HOME_PATH . "/Nginx/" . self::PREFIX . "{$name}.conf";
        file_put_contents($nginxConfigPath, $template);

        $this->brew->restartService($this->brew->nginxServiceName());

        info("Link create success");
        table([
            ['domain', $domain],
            ['php version', $version],
            ['web root', $root],
            ['fpm socket', $socket],
            ['url', "http://{$domain}"],
        ]);
    }

    protected function showAvailablePhpVersions()
    {
        $versions = [];
        foreach (new \DirectoryIterator($this->phpBrewRoot . '/php/') as $dir) {
            if (strpos($dir->getFilename(), 'php-') === 0) {
                $versions[] = [
                    'version' => substr($dir->getFilename(), 4),
                    'fpm' => file_exists($dir->getPathname() . "/sbin/php-fpm") ? '' : 'fpm not install',
                ];
            }
        }
        output("");
        output('Tips: run `phpbrew use VERSION` to enable target version, or run `phpbrew:list VERSION` with version argument.');
        output('Available PHP versions:');
        table(['version', 'fpm'], $versions);
    }

    public function links()
    {
        $it = new \DirectoryIterator(VALET_HOME_PATH . '/Nginx');
        $sites = [];
        foreach ($it as $file) {
            if (strpos($file->getFilename(), self::PREFIX) === 0) {
                $name = substr($file->getFilename(), strlen(self::PREFIX), -5);
                list($root, $version) = $this->parseRootAndVersion($file->getPathname());
                $sites[] = [
                    'name' => $name,
                    'url' => "http://{$name}.{$this->getTld()}",
                    'version' => $version,
                    'root' => $root,
                ];
            }
        }
        table(['Site', 'Url', 'Version', 'Path'], $sites);
    }

    public function unlink($name)
    {
        $name = $name ?: basename(getcwd());
        $nginxConfigPath = VALET_HOME_PATH . "/Nginx/" . self::PREFIX . "{$name}.conf";
        if (file_exists($nginxConfigPath)) {
            unlink($nginxConfigPath);
            $this->brew->restartService($this->brew->nginxServiceName());
            info("Site $name unlinked");
        } else {
            warning("Site {$name} not found.");
        }
    }

    protected function parseRootAndVersion($path)
    {
        $content = file_get_contents($path);
        preg_match('/root\ (.*);/', $content, $rootMatch);
        preg_match('/\/php\/php-(.*)\/var/', $content, $versionMatch);
        return [
            isset($rootMatch[1]) ? $rootMatch[1] : null,
            isset($versionMatch[1]) ? $versionMatch[1] : null,
        ];
    }

    protected function getTld()
    {
        return $this->config->read()['tld'];
    }

}
