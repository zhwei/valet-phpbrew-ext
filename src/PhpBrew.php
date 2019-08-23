<?php

namespace Zhwei\ValetPhpBrewExt;

use Valet\Brew;
use Valet\Configuration;
use function Valet\info;
use function Valet\table;
use function Valet\warning;

class PhpBrew
{
    const PREFIX = '__phpbrew.';

    /**
     * @var string
     */
    protected $phpBrewRoot;
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
            warning('Only support laravel/lumen now');
            return;
        }

        $phpDir = $this->phpBrewRoot . "/php/php-{$version}";
        if (!file_exists($phpDir)) {
            warning("php version {$version} not found in {$phpDir}");
            return;
        }

        $root = getcwd() . '/public';
        $name = $name ?: basename(getcwd());
        $domain = $name . '.' . $this->getTld();

        $socket = $this->getSocketPath($version);
        if (!file_exists($socket)) {
            warning("socket not found: {$socket}");
            warning("please try `phpbrew fpm restart`");
            return;
        }

        info("domain: {$domain}");
        info("web root: {$root}");
        info("fpm socket: {$socket}");

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

        info("nginx config create success, try visit: http://{$domain}");
    }

    public function links()
    {
        $it = new \DirectoryIterator(VALET_HOME_PATH . '/Nginx');
        $sites = [];
        foreach ($it as $file) {
            if (strpos($file->getFilename(), self::PREFIX) === 0) {
                $name = substr($file->getFilename(), strlen(self::PREFIX), -5);
                $sites[] = [
                    'name' => $name,
                    'url' => "http://{$name}.{$this->getTld()}",
                    'root' => $this->parseRoot($file->getPathname()),
                ];
            }
        }
        table(['Site', 'Url', 'Path'], $sites);
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

    protected function parseRoot($path)
    {
        $content = file_get_contents($path);
        preg_match('/root\ (.*);/', $content, $match);
        return isset($match[1]) ? $match[1] : null;
    }

    protected function getTld()
    {
        return $this->config->read()['tld'];
    }


}
