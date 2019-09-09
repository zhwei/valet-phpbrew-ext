<?php

namespace Zhwei\ValetPhpBrewExt;

use Illuminate\Container\Container;
use Silly\Application;
use Valet\Configuration;

class CommandRegister
{

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var PhpBrew
     */
    protected $phpBrew;

    public function __construct(Configuration $config, PhpBrew $phpBrew)
    {
        $this->config = $config;
        $this->phpBrew = $phpBrew;
    }

    public function register(Application $app)
    {
        /** @var PhpBrew $phpBrew */
        $phpBrew = Container::getInstance()->make(PhpBrew::class);

        $app->command(
            'phpbrew:link [phpVersion] [name]',
            function ($phpVersion = null, $name = null) use ($phpBrew) {
                $phpBrew->link($phpVersion, $name);
            }
        )->descriptions('[PHPBrewExt] 添加站点并指定 PHP 版本');

        $app->command('phpbrew:links', [$phpBrew, 'links'])
            ->descriptions('[PHPBrewExt] 列出所有站点');

        $app->command('phpbrew:unlink [name]', function ($name = null) use ($phpBrew) {
            $phpBrew->unlink($name);
        })->descriptions('[PHPBrewExt] 移除站点');
    }
}
