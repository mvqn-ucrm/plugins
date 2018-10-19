<?php

use MVQN\UCRM\Plugins\Log;
use MVQN\UCRM\Plugins\Plugin;

class RestClientTests extends PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $path = Plugin::projectRoot(__DIR__."/../../../../examples/plugin-example/");
        echo "Using '$path' as the root path for testing purposes!\n\n";

    }

    public function testPaths()
    {
        echo "ROOT: ".Plugin::projectRoot()."\n";

        $zip = Plugin::usingZip() ? "T" : "F";
        echo "ZIP?: ".$zip."\n";

        $data = Plugin::dataPath();
        echo "DATA: ".$data."\n";
    }



}