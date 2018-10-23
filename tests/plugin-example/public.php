<?php
declare(strict_types=1);

require_once __DIR__."vendor/autoload.php";
include_once __DIR__."/bootstrap.php";

use MVQN\UCRM\Plugins\Log;

(function()
{
    Log::write("TEST!");

})();