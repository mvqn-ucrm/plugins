<?php
declare(strict_types=1);

namespace MVQN\UCRM\Plugins;

use MVQN\Dynamics\AutoObject;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Constant;

abstract class SettingsBase extends AutoObject
{
    /** @var Constant[] */
    private static $constants;

    /**
     * @param string $name The name of the constant to append to this Settings class.
     * @param mixed $value The value of the constant to append to this Settings class.
     * @param string $comment An optional comment for this constant.
     * @throws \Exception
     */
    public static function addConstant(string $name, $value, string $comment = "")
    {
        // Parse the previously generated class!
        // NOTE: It will not exist in this library, but would be valid in the downstream project!
        $class = ClassType::from(Settings::class);
        $namespace = $class->getNamespace()->getName();

        // Get the file's path from it's own 'const'.
        //$filePath = $class->getConstants()["FILE_PATH"]->getValue();

        if(self::$constants === null)
            self::$constants = [];

        if(array_key_exists($name, self::$constants))
            return; // Already Exists!

        $constant = new Constant($name);

        if($comment)
            $constant->setComment("@const ".gettype($value)." ".$comment);
        else
            $constant->setComment("@const ".gettype($value));

        $constant
            ->setVisibility("public")
            ->setValue($value);

        self::$constants[] = $constant;

        SettingsBuilder::generate($namespace, self::$constants);
    }



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