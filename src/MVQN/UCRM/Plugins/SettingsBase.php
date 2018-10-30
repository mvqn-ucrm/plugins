<?php
declare(strict_types=1);

namespace MVQN\UCRM\Plugins;

use MVQN\Dynamics\AutoObject;

/**
 * Class SettingsBase
 *
 * @package MVQN\UCRM\Plugins
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 */
abstract class SettingsBase extends AutoObject
{
    /**
     * @return bool
     * @throws Exceptions\PluginNotInitializedException
     */
    protected static function __beforeFirstStaticCall(): bool
    {
        // Get the child class that is calling this function.
        $class = get_called_class();

        // Get this Plugin's data path.
        $path = Plugin::getDataPath();

        // IF the 'data/config.json' file does NOT exist, THEN return failure!
        if(!file_exists("$path/config.json"))
            return false;

        // Convert the 'data/config.json' into an associative array.
        $settings = json_decode(file_get_contents("$path/config.json"), true) ?: [];

        // Loop through each key/value pair of the config...
        foreach($settings as $key => $value)
            // IF the property matches one from the calling class...
            if(property_exists($class, $key))
                // THEN set that property of the child/calling class to the value from the config.
                $class::$$key = $value;

        // Return success!
        return true;
    }




}