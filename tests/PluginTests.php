<?php
declare(strict_types=1);

use MVQN\UCRM\Plugins\Plugin;
use MVQN\UCRM\Plugins\Log;
use MVQN\UCRM\Plugins\LogEntry;

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

    public function testLogFile()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $logFile = Log::logFile();
        echo "Log::logFile()                 = $logFile\n";
        $this->assertFileExists(Plugin::getDataPath()."/plugin.log");

        echo "\n";
    }

    public function testLogsPath()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $logsPath = Log::logsPath();
        echo "Log::logsPath()                = $logsPath\n";
        $this->assertDirectoryExists(Plugin::getDataPath()."/logs/");

        $logsPath = Log::logsPath(new DateTimeImmutable("10/20/2018"));
        echo "Log::logsPath('10/20/2018')    = $logsPath\n";
        $this->assertFileExists(Plugin::getDataPath()."/logs/2018-10-20.log");

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testLogWrite()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $message = "This is a test message!";
        echo "Log::write('$message')         = ".Log::write($message)."\n";
        $this->assertEquals($message, Log::line(-1)->getText());

        echo "\n";
    }

    public function testLogWriteArray()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $array = [ "testNumber" => 1, "testType" => "string", "testDate" => new DateTimeImmutable() ];
        echo "Log::writeArray(array)         = ".Log::writeArray($array)."\n";
        $this->assertEquals(
            json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), (string)Log::line(-1)->getText());

        echo "\n";
    }

    public function testLogWriteObject()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $entry = new LogEntry(new DateTimeImmutable(), LogEntry::SEVERITY_WARNING, "This is a test!");
        echo "Log::writeObject(entry)        = ".Log::writeObject($entry)."\n";
        $this->assertEquals(
            json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), (string)Log::line(-1)->getText());

        echo "\n";
    }

    public function testLogDebug()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $message = "This is a test message!";
        echo "Log::debug(message)            = ".Log::debug($message)."\n";
        $this->assertEquals($message, Log::line(-1)->getText());

        echo "\n";
    }

    public function testLogInfo()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $message = "This is a test message!";
        echo "Log::info(message)             = ".Log::info($message)."\n";
        $this->assertEquals($message, Log::line(-1)->getText());

        echo "\n";
    }

    public function testLogWarning()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $message = "This is a test message!";
        echo "Log::warning(message)          = ".Log::warning($message)."\n";
        $this->assertEquals($message, Log::line(-1)->getText());

        echo "\n";
    }

    public function testLogError()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $message = "This is a test message!";
        echo "Log::error(message)            = ".Log::error($message)."\n";
        $this->assertEquals($message, Log::line(-1)->getText());

        $exception = \MVQN\UCRM\Plugins\Exceptions\PluginNotInitializedException::class;
        $this->expectException($exception);
        $message = "This is a test message!";
        echo "Log::error(message, exception) = ".Log::error($message, $exception)."\n";

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testLogLines()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        Log::write("This is a test line 1");
        Log::write("This is a test line 2");
        Log::write("This is a test line 3 ");
        Log::write("This is a test line 4   ");

        Log::debug("This is a debug line!");
        Log::info("This is an info line!");
        Log::warning("This is a warning line!");

        $lines = Log::lines(1, 3);
        echo "Log::lines(1, 3)               >\n";
        echo $lines."\n";
        $this->assertCount(3, $lines);

        echo "\n";
    }

    public function testLogTail()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        Log::write("This is a test line 1");
        Log::write("This is a test line 2");
        Log::write("This is a test line 3 ");
        Log::write("This is a test line 4   ");

        Log::debug("This is a debug line!");
        Log::info("This is an info line!");
        Log::warning("This is a warning line!");

        $lines = Log::tail(2);
        echo "Log::tail(2)                   >\n";
        echo $lines."\n";
        $this->assertCount(2, $lines);

        echo "\n";
    }

    public function testLogLine()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        Log::write("This is a test line 1");
        Log::write("This is a test line 2");
        Log::write("This is a test line 3 ");
        Log::write("This is a test line 4   ");

        Log::debug("This is a debug line!");
        Log::info("This is an info line!");
        Log::warning("This is a warning line!");

        $line = Log::line(3);
        echo "Log::line(3)                   = ";
        echo $line."\n";
        $this->assertEquals("This is a test line 4", $line->getText());

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testLogClear()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        echo "Log::clear()                   > ";
        Log::clear();
        echo "CLEARED!\n";
        $this->assertCount(1, Log::lines());

        echo "\n";
    }

    public function testLogIsEmpty()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        Log::write("This is a test line!");

        echo "Log::isEmpty()                 = ".(Log::isEmpty() ? "TRUE" : "FALSE")."\n";
        $this->assertFalse(Log::isEmpty());

        Log::clear(false);
        echo "Log::isEmpty()                 = ".(Log::isEmpty() ? "TRUE" : "FALSE")."\n";
        $this->assertTrue(Log::isEmpty());

        echo "\n";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testBetween()
    {
        Plugin::initialize(__DIR__."/plugin-example/");
        Log::clear();

        $lines = Log::between(new DateTimeImmutable("2018-10-20 17:49:10"), new DateTimeImmutable("2018-10-22"));
        echo "Log::between(start, end)       = [{$lines->count()}] $lines\n";
        $this->assertCount(7, $lines);

        $lines = Log::between(new DateTimeImmutable("2018-10-20 17:49:10"));
        echo "Log::between(start)            = [{$lines->count()}] $lines\n";
        $this->assertCount(7 + Log::lines()->count(), $lines);

        echo "";
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function testRotate()
    {
        Plugin::initialize(__DIR__."/plugin-example/");

        $today = new DateTimeImmutable((new DateTime())->format(Log::TIMESTAMP_FORMAT_DATEONLY));
        echo "Log::rotate()                  = ".Log::rotate()."\n";
        $this->assertCount(Log::lines()->count(), Log::between($today));

        echo "\n";
    }




}