<?php
declare(strict_types=1);
require_once __DIR__."/vendor/autoload.php";

/* START: REQUIRED PATH FOR DEVELOPMENT TESTS */
require_once __DIR__."/../../vendor/autoload.php";
/* END: REQUIRED PATH FOR DEVELOPMENT TESTS */

use MVQN\UCRM\Plugins\Plugin;

// Regenerate the Settings class, in case anything has changed in the manifest.json file.
Plugin::initialize(__DIR__);
Plugin::createSettings();

// Load any ENV variables from '.env' if it exists.
if(file_exists(__DIR__."/.env"))
    $env = (new \Dotenv\Dotenv(__DIR__))->load();

