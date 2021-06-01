<?php

namespace TAS\Definition\PageObject;

use TAS\Adaption\Browser\CustomWebDriver;
use TAS\Helper\ConfigurationManager;

abstract class PageConfigurationManager extends CustomWebDriver
{
    protected $pageName;
    protected $module;

    protected function loadConfiguration($section, array $args = [])
    {
        if (isset($args['module'])) {
            $this->module = $args['module'];
            $iniPath = str_replace(
                '{moduleName}',
                strtolower($this->module),
               BEHAT_PAGE_MODULE_CONFIGURATION
            );
        } else {
            throw new \RuntimeException('No Module set for page configuration.');
        }
        if (!file_exists($iniPath)) {
            throw new \RuntimeException(
                "Path to page configuration file is incorrect: $iniPath"
            );
        }
        $this->pageName = $section;
        $configuration =  ConfigurationManager::loadConfiguration(
            $iniPath,
            $section
        );

        $configurationArray = $this->parseConfiguration($configuration);
        foreach ($configurationArray as $key => $item) {
            $this->{$key} = $item;
        }
    }

    public function getPageName()
    {
        return $this->pageName;
    }

    private function stripUnderscoreFromArrayKeysRecursive($array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            $newArray[str_replace('_', ' ', $key)] = $this->parseConfiguration($value);
        }
        return $newArray;
    }

    private function parseConfiguration($value)
    {
        return is_array($value)
            ? $this->stripUnderscoreFromArrayKeysRecursive($value)
            : $value;
    }
}