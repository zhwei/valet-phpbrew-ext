<?php

use Illuminate\Container\Container;
use Silly\Application;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Create the application.
 */
Container::setInstance(new Container);
$app = new Application('valet phpbrew ext tester');

include __DIR__ . '/../phpbrew-ext.php';


$app->run();
