<?php
declare(strict_types=1);
include_once __DIR__."/bootstrap.php";

/* START: REQUIRED PATH FOR DEVELOPMENT TESTS */
include_once __DIR__."/src/MVQN/UCRM/Plugins/Settings.php";
/* END: REQUIRED PATH FOR DEVELOPMENT TESTS */

use MVQN\UCRM\Plugins\Plugin;
use MVQN\UCRM\Plugins\Log;
use MVQN\UCRM\Plugins\Settings;

(function()
{
    echo Settings::PLUGIN_ROOT_PATH."\n";
    $lang = Settings::getLanguage();

    echo $lang."\n";
    echo "";

    //Log::write("Starting tests!");

})();