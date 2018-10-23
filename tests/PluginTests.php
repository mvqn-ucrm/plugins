<?php
declare(strict_types=1);

use MVQN\UCRM\Plugins\Plugin;
use MVQN\UCRM\Plugins\Log;

/**
 * Class PluginTests
 */
class PluginTests extends PHPUnit\Framework\TestCase
{
    // =================================================================================================================
    // HELPERS
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Only used in this Test class as a way to "uninitialize" the Plugin for testing Exceptions.
     *
     * @throws ReflectionException
     */
    private function uninitializePlugin()
    {
        $plugin = new ReflectionClass(Plugin::class);

        $rootPath = $plugin->getProperty("_rootPath");
        $ignoreCache = $plugin->getProperty("_ignoreCache");
        $settingsFile = $plugin->getProperty("_settingsFile");

        if($rootPath)
        {
            $rootPath->setAccessible(true);
            $rootPath->setValue("");
        }

        if($ignoreCache)
        {
            $ignoreCache->setAccessible(true);
            $ignoreCache->setValue(null);
        }

        if($settingsFile)
        {
            $settingsFile->setAccessible(true);
            $settingsFile->setValue("");
        }
    }

    // =================================================================================================================
    // PATHS
    // -----------------------------------------------------------------------------------------------------------------

    public function testGetRootPath()
    {
        Plugin::initialize(__DIR__ . "/plugin-example/");

        $root = Plugin::getRootPath();
        echo "Plugin::getRootPath()          = $root\n";
        $this->assertFileExists($root);

        echo "\n";
    }

    public function testGetDataPath()
    {
        Plugin::initialize(__DIR__ . "/plugin-example/");

        $data = Plugin::getDataPath();
        echo "Plugin::getDataPath()          = $data\n";
        $this->assertFileExists($data);

        echo "\n";
    }

    public function testGetSourcePath()
    {
        Plugin::initialize(__DIR__ . "/plugin-example/");

        $source = Plugin::getSourcePath();
        echo "Plugin::getSourcePath()        = $source\n";
        $this->assertFileExists($source);

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testGetRootPathUninitialized()
    {
        $this->uninitializePlugin();

        $this->expectException("MVQN\UCRM\Plugins\Exceptions\PluginNotInitializedException");

        $root = Plugin::getRootPath();
        echo "Plugin::getRootPath()          = $root\n";
        $this->assertFileExists($root);

        echo "\n";
    }

    public function testGetDataPathUninitialized()
    {
        $this->uninitializePlugin();

        $this->expectException("MVQN\UCRM\Plugins\Exceptions\PluginNotInitializedException");

        $data = Plugin::getDataPath();
        echo "Plugin::getDataPath()          = $data\n";
        $this->assertFileExists($data);

        echo "\n";
    }

    public function testGetSourcePathUninitialized()
    {
        $this->uninitializePlugin();

        $this->expectException("MVQN\UCRM\Plugins\Exceptions\PluginNotInitializedException");

        $source = Plugin::getSourcePath();
        echo "Plugin::getSourcePath()        = $source\n";
        $this->assertFileExists($source);

        echo "\n";
    }

    // =================================================================================================================
    // BUNDLING
    // -----------------------------------------------------------------------------------------------------------------

    public function testBundle()
    {
        Plugin::initialize(__DIR__ . "/plugin-example/");

        echo "Plugin::bundle()               >\n";
        Plugin::bundle();
        $this->assertFileExists(__DIR__ . "/plugin-example/plugin-example.zip");

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testBundleUninitialized()
    {
        echo "Plugin::bundle()               >\n";
        Plugin::bundle(__DIR__ . "/plugin-example", "plugin-test");
        $this->assertFileExists(__DIR__ . "/plugin-example/plugin-test.zip");

        echo "\n";
    }

    // =================================================================================================================
    // SETTINGS
    // -----------------------------------------------------------------------------------------------------------------

    public function testSettings()
    {
        Plugin::initialize(__DIR__ . "/plugin-example/");

        Plugin::createSettings();
        Plugin::appendSettingsConstant("TEST", "TEST", "This is a test setting with a comment!");

        // Must be included AFTER Plugin::appendSettingsConstant() to see any custom constants!
        require_once __DIR__."/plugin-example/src/MVQN/UCRM/Plugins/Settings.php";

        echo "Plugin::createSettings()\n";

        $rootPath = \MVQN\UCRM\Plugins\Settings::PLUGIN_ROOT_PATH;
        echo "Settings::PLUGIN_ROOT_PATH     = $rootPath\n";
        $this->assertEquals(realpath(__DIR__."/plugin-example/"), $rootPath);

        $test = \MVQN\UCRM\Plugins\Settings::TEST;
        echo "Settings::TEST                 = $test\n";
        $this->assertEquals("TEST", $test);

        echo "\n";
    }

    public function testSettingsCustom()
    {
        Plugin::initialize(__DIR__ . "/plugin-example/");

        Plugin::createSettings("Plugin", "TestSettings");
        Plugin::appendSettingsConstant("TEST", "TEST", "This is a test setting with a comment!");

        // Must be included AFTER Plugin::appendSettingsConstant() to see any custom constants!
        require_once __DIR__."/plugin-example/src/Plugin/TestSettings.php";

        echo "Plugin::createSettings('Plugin', 'TestSettings')\n";

        $rootPath = \Plugin\TestSettings::PLUGIN_ROOT_PATH;

        echo "TestSettings::PLUGIN_ROOT_PATH = $rootPath\n";
        $this->assertEquals(realpath(__DIR__."/plugin-example/"), $rootPath);

        $test = \Plugin\TestSettings::TEST;
        echo "TestSettings::TEST             = $test\n";
        $this->assertEquals("TEST", $test);

        echo "\n";
    }

    public function testAppendSettingsConstant()
    {
        Plugin::initialize(__DIR__ . "/plugin-example/");

        Plugin::createSettings("MVQN\\UCRM\\Plugins", "Settings2");
        Plugin::appendSettingsConstant("TEST_BOOLEAN", false);
        Plugin::appendSettingsConstant("TEST_INTEGER", 1234);
        Plugin::appendSettingsConstant("TEST_DOUBLE", 1.2345);
        Plugin::appendSettingsConstant("TEST_STRING", "TEST", "This is a test setting with a comment!");
        Plugin::appendSettingsConstant("TEST_ARRAY", ["1234", "5678"], "This is a test setting that is not supported!");

        // Must be included AFTER Plugin::appendSettingsConstant() to see any custom constants!
        require_once __DIR__."/plugin-example/src/MVQN/UCRM/Plugins/Settings2.php";

        echo "Plugin::createSettings('MVQN\UCRM\Plugins', 'Settings2')\n";

        $rootPath = \MVQN\UCRM\Plugins\Settings2::PLUGIN_ROOT_PATH;
        echo "Settings2::PLUGIN_ROOT_PATH    = $rootPath\n";
        $this->assertEquals(realpath(__DIR__."/plugin-example/"), $rootPath);

        $testString = \MVQN\UCRM\Plugins\Settings2::TEST_STRING;
        echo "TestSettings::TEST_STRING      = $testString\n";
        $this->assertEquals("TEST", $testString);

        $testInteger = \MVQN\UCRM\Plugins\Settings2::TEST_INTEGER;
        echo "TestSettings::TEST_INTEGER     = $testInteger\n";
        $this->assertEquals(1234, $testInteger);

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testSettingsValues()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        // Must be included AFTER Plugin::appendSettingsConstant() to see any custom constants!
        require_once __DIR__."/plugin-example/src/MVQN/UCRM/Plugins/Settings.php";

        $debugEnabled = \MVQN\UCRM\Plugins\Settings::getDebugEnabled();
        echo "Plugin::getDebugEnabled()      = $debugEnabled\n";
        $this->assertEquals(true, $debugEnabled);

        $language = \MVQN\UCRM\Plugins\Settings::getLanguage();
        echo "Plugin::getLanguage()          = $language\n";
        $this->assertEquals("es_ES", $language);

        echo "\n";
    }

    // =================================================================================================================
    // LOGGING
    // -----------------------------------------------------------------------------------------------------------------

    public function testLogClear()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        echo "Log::clear()                   > ";
        Log::clear();
        $this->assertCount(0, Log::lines());
        echo "CLEARED!\n";

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testLogLines()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        Log::write("This is a test line 1");
        Log::write("This is a test line 2");
        Log::write("This is a test line 3");
        Log::write("This is a test line 4");
        Log::write("This is a test line 5");

        $lines = Log::lines(1, 3);
        echo "Log::lines(1, 3)               >\n";
        print_r($lines);
        $this->assertCount(3, $lines);
        echo "\n";

        $lines = Log::lines(1, 3, true);
        echo "Log::lines(1, 3, true)         >\n";
        print_r($lines);
        $this->assertCount(3, $lines);
        echo "\n";
    }

    public function testLogTail()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        Log::write("This is a test line 1");
        Log::write("This is a test line 2");
        Log::write("This is a test line 3");
        Log::write("This is a test line 4");
        Log::write("This is a test line 5");

        $lines = Log::tail(2);
        echo "Log::tail(2)                   >\n";
        print_r($lines);
        $this->assertCount(2, $lines);
        echo "\n";

        $lines = Log::tail(2, true);
        echo "Log::tail(2, true)             >\n";
        print_r($lines);
        $this->assertCount(2, $lines);
        echo "\n";
    }

    public function testLogLine()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        Log::write("This is a test line 1");
        Log::write("This is a test line 2");
        Log::write("This is a test line 3");
        Log::write("This is a test line 4");
        Log::write("This is a test line 5");

        $line = Log::line(3);
        echo "Log::line(3)                   = ";
        print_r($line);
        $this->assertEquals("This is a test line 4", $line);
        echo "\n";

        $line = Log::line(2, true);
        echo "Log::line(2, true)             = ";
        print_r($line);
        $this->assertStringEndsWith("] This is a test line 3", $line);
        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testLogWrite()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $message = "This is a test message!";
        echo "Log::write('$message')\n";
        Log::write($message);
        $this->assertEquals($message, Log::line(-1, false));
        echo "\n";
    }


    // -----------------------------------------------------------------------------------------------------------------



    public function testBetween()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $lines = Log::between(new DateTime("2018-10-20 17:49:10"), new DateTime(), true);

        $test = Log::load(new DateTime("2018-10-22"));

        echo "";
    }




    // -----------------------------------------------------------------------------------------------------------------



    public function testRotate()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        Log::rotate();
    }




}