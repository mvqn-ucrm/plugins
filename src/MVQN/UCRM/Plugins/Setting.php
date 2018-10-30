<?php
declare(strict_types=1);

namespace MVQN\UCRM\Plugins;

/**
 * Class Setting
 *
 * @package MVQN\UCRM\Plugins
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class Setting
{
    /** @var string */
    public $key;

    /** @var string */
    public $label;

    /** @var string */
    public $description = "";

    /** @var int */
    public $required = 1;

    /** @var string */
    public $type = "text";

    /** @var array */
    public $choices = [];

    /**
     * @param array $array An array of data to use when populating the properties of this object.
     * @throws Exceptions\ManifestElementException
     */
    public function __construct(array $array)
    {
        if(!array_key_exists("key", $array) || $array["key"] === null || $array["key"] === "")
            throw new Exceptions\ManifestElementException(
                "A 'key' element is missing from the 'manifest.json' file!");

        $this->key = $array["key"];

        if(!array_key_exists("label", $array) || $array["label"] === null || $array["label"] === "")
            throw new Exceptions\ManifestElementException(
                "A 'label' element is missing from the 'manifest.json' file!");

        $this->label = $array["label"];

        $this->description = array_key_exists("description", $array) && $array["description"] !== null ? $array["description"] : "";
        $this->required = array_key_exists("required", $array) && $array["required"] !== null ? (bool)$array["required"] : true;

        $type = array_key_exists("type", $array) && $array["type"] !== null ? $array["type"] : "text";

        switch($type)
        {
            case "text":
            case "textarea":
            case "choice":
            case "file":
                $this->type = "string";
                break;
            case "checkbox":
                $this->type = "bool";
                break;
            case "date":
            case "datetime":
                $this->type = "\DateTime";
                break;
            default:
                throw new Exceptions\ManifestElementException("Unknown 'type' found in manifest.json!");
        }

        $this->choices = array_key_exists("choices", $array) && $array["choices"] !== null ? $array["choices"] : [];
    }

}

