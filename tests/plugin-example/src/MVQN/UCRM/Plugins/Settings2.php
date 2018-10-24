<?php
declare(strict_types=1);

namespace MVQN\UCRM\Plugins;

/**
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 * @method static bool|null getDebugEnabled()
 * @method static string|null getLanguage()
 */
final class Settings2 extends SettingsBase
{
	/** @const string The absolute path to the root path of this project. */
	public const PLUGIN_ROOT_PATH = 'C:\Users\rspaeth\Documents\PhpStorm\Projects\mvqn\ucrm\plugins\tests\plugin-example';

	/** @const string The absolute path to the data path of this project. */
	public const PLUGIN_DATA_PATH = 'C:\Users\rspaeth\Documents\PhpStorm\Projects\mvqn\ucrm\plugins\tests\plugin-example\data';

	/** @const string The absolute path to the source path of this project. */
	public const PLUGIN_SOURCE_PATH = 'C:\Users\rspaeth\Documents\PhpStorm\Projects\mvqn\ucrm\plugins\tests\plugin-example\src';

	/** @const string The publicly accessible URL of this UCRM, null if not configured in UCRM. */
	public const UCRM_PUBLIC_URL = 'http://ucrm.example.com/';

	/** @const string An automatically generated UCRM API 'App Key' with read/write access. */
	public const PLUGIN_APP_KEY = 'CVng+dj2tQLI9Gl3P8Fk0birJkDksytjlud2rzV9v8LSbkuxfmYWj1Buvxp5WhyG';

	/** @const bool */
	public const TEST_BOOLEAN = false;

	/** @const int */
	public const TEST_INTEGER = 1234;

	/** @const float */
	public const TEST_DOUBLE = 1.2345;

	/** @const string This is a test setting with a comment! */
	public const TEST_STRING = 'TEST';

	/**
	 * Debug Enabled?
	 * @var bool|null If enabled, verbose debug messages are sent to the log.
	 */
	protected static $debugEnabled;

	/**
	 * Language
	 * @var string|null The desired language for notifications.
	 */
	protected static $language;
}
