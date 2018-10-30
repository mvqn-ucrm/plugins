<?php

require __DIR__.'/../../vendor/autoload.php';

use MVQN\UCRM\Plugins\Plugin;

switch($argv[1])
{
    case "create":
        Plugin::initialize(__DIR__);
        Plugin::createSettings();
        break;

    case "bundle":
        Plugin::initialize(__DIR__);
        Plugin::bundle();
        break;

    // TODO: More commands to come!

    default:
        break;
}


