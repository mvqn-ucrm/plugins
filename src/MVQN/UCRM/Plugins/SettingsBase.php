<?php
declare(strict_types=1);

namespace MVQN\UCRM\Plugins;

use MVQN\Dynamics\AutoObject;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Constant;

abstract class SettingsBase extends AutoObject
{


    protected static function __beforeFirstCall(): bool
    {
        $class = get_called_class();

        $path = Plugin::dataPath();

        if(!file_exists("$path/config.json"))
            return false;

        $settings = json_decode(file_get_contents("$path/config.json"), true);

        foreach($settings as $key => $value)
            if(property_exists($class, $key))
                $class::$$key = $value;

        return true;
    }



}