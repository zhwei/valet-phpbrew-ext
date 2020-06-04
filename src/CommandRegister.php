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
        )->descriptions(
            '[PHPBrewExt] Add site use the specified PHP version',
            [
                'phpVersion' => "(optional) if not provide, will parse from \$_SERVER['_']",
                'name' => "(optional) if not provide, will use current folder name",
            ]
        );

        $app->command('phpbrew:links', [$phpBrew, 'links'])
            ->descriptions('[PHPBrewExt] List all sites created by phpbrew extension');

        $app->command('phpbrew:unlink [name]', function ($name = null) use ($phpBrew) {
            $phpBrew->unlink($name);
        })->descriptions('[PHPBrewExt] Remove site, `name` default is current folder name.');
    }
}
