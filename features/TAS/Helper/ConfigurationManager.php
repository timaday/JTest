<?php


namespace TAS\Helper;

use  Laminas\Config\Reader\Ini;


class ConfigurationManager
{
    public static function loadConfiguration($path, $section)
    {
        $reader = new Ini();
        $reader->getProcessSections(true);
        $iniData = self::parseIniData($reader->fromFile($path));
        if (!isset($iniData[$section])) {
            throw new \InvalidArgumentException(
                "Cannot load configuration - Section: '$section' was not found in file: $path"
            );
        }
        return $iniData[$section];
    }

    private static function parseIniData($iniData)
    {
        $config = array();
        foreach($iniData as $namespace => $properties){
            if (strpos( $namespace, ':') === false) {
                $name = $namespace;
                // create namespace if necessary
                if(!isset($config[$name])) {
                    $config[$name] = array();
                    foreach($iniData[$name] as $prop => $val) {
                        $config[$name][$prop] = $val;
                    }
                }
            } else {
            list($name, $extends) = explode(':', $namespace);
            $name = trim($name);
            $extends = trim($extends);
            // create namespace if necessary
            if(!isset($config[$name])) {
                $config[$name] = array();
            }
            // inherit base namespace
            if(isset($iniData[$extends])){
                foreach($iniData[$extends] as $prop => $val) {
                    $config[$name][$prop] = $val;
                }
            }
            }
            // overwrite / set current namespace values
            foreach($properties as $prop => $val) {
                $config[$name][$prop] = $val;
            }
        }
        return $config;
    }

}