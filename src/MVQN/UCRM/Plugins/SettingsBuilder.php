<?php
declare(strict_types=1);

namespace MVQN\UCRM\Plugins;

use Nette\PhpGenerator\Constant;
use Nette\PhpGenerator\PhpNamespace;

final class SettingsBuilder
{
    private const CLASS_NAME = "Settings";
    private const CLASS_NAMESPACE = "MVQN\\UCRM\\Plugins";

    /**
     * Generates a class with auto-implemented methods and then saves it to a PSR-4 compatible file.
     * @param Constant[] An optional list of constants to append to the class.
     * @throws \Exception Throws an Exception if any errors occur.
     */
    public static function generate(array $constants = []): void
    {
        $root = Plugin::usingZip() ? Plugin::projectRoot()."/zip/" : Plugin::projectRoot();
        $path = $root."src/".str_replace("\\", "/", self::CLASS_NAMESPACE);

        if(!file_exists($root."/manifest.json"))
            return;

        if(!file_exists($path))
            mkdir($path, 0777, true);

        $data = json_decode(file_get_contents($root."/manifest.json"), true);
        $data = array_key_exists("configuration", $data) ? $data["configuration"] : [];

        $_namespace = new PhpNamespace(self::CLASS_NAMESPACE);
        //$_namespace->addUse("MVQN\\UCRM\\Plugins\\SettingsBase");
        $_namespace->addUse(SettingsBase::class);

        $_class = $_namespace->addClass(self::CLASS_NAME);
        $_class
            ->setFinal()
            ->setExtends(SettingsBase::class)
            ->addComment("@author Ryan Spaeth <rspaeth@mvqn.net>\n");

        $filePath = realpath($path."/".self::CLASS_NAME.".php");

        $_class->addConstant("FILE_PATH", $filePath)
            ->setVisibility("protected")
            ->addComment("@const string The full path of this Settings file.");

        if(file_exists($root."/ucrm.json"))
        {
            $ucrm = json_decode(file_get_contents($root."/ucrm.json"), true);

            $_class->addConstant("UCRM_PUBLIC_URL", $ucrm["ucrmPublicUrl"])
                ->setVisibility("public")
                ->addComment("@const string The publicly accessible URL of this UCRM, null if not configured in UCRM.");

            // Seems to be missing from the latest builds of UCRM ???
            if(array_key_exists("pluginPublicUrl", $ucrm))
            {
                $_class->addConstant("PLUGIN_PUBLIC_URL", $ucrm["pluginPublicUrl"])
                    ->setVisibility("public")
                    ->addComment("@const string The publicly accessible URL assigned to this Plugin by the UCRM.");
            }

            $_class->addConstant("PLUGIN_APP_KEY", $ucrm["pluginAppKey"])
                ->setVisibility("public")
                ->addComment("@const string An automatically generated UCRM API 'App Key' with read/write access.");
        }

        foreach($constants as $constant)
            $_class->addMember($constant);

        foreach($data as $setting)
        {
            $_setting = new Setting($setting);

            $type = $_setting->type.(!$_setting->required ? "|null" : "");

            $_property = $_class->addProperty($_setting->key);
            $_property
                ->setVisibility("protected")
                ->setStatic()
                ->addComment("{$_setting->label}")
                ->addComment("@var {$type} {$_setting->description}");

            $getter = "get".ucfirst($_setting->key);

            $_class->addComment("@method static $type $getter()");
        }

        $code =
            "<?php\n".
            "declare(strict_types=1);\n".
            "\n".
            $_namespace;

        // Hack to add extra line return between const declarations...
        $code = str_replace(";\n\t/** @const", ";\n\n\t/** @const", $code);

        file_put_contents($path."/".self::CLASS_NAME.".php", $code);

    }


}