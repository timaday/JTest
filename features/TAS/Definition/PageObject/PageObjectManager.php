<?php

namespace TAS\Definition\PageObject;

use TAS\Adaption\Browser\CustomWebDriver;

class PageObjectManager
{
    protected $pageObject;
    protected $pageName;
    protected $driver;
    protected $module;
    protected $pageRoot = '\TAS\Definition\PageObject\Pages';

    public function __construct($module)
    {
        $this->setModule($module);

    }

    private function setModule($module)
    {
        $this->module = ucfirst(preg_replace( '/[\W]/', '', strtolower(trim($module))));
    }

    /**
     * @param null $pageName
     * @param null $driver
     * @return PageObjectSupport
     */
    public function getPage($pageName = null, $driver = null, array $args = [])
    {
        if ($driver instanceof CustomWebDriver) {
            $this->driver = $driver;
        }
        $pageNameLower = strtolower($pageName);
        if ($this->pageName() === $pageNameLower) {
            return $this->pageObject;
        }
        $args['module'] = $this->module;
        $this->pageObject = null;

        $pageNameFormatted = $this->classStringFormatter($pageName);
        $className = $this->pageRoot . $pageNameFormatted ;
        $classNameInModule = $this->pageRoot . '\\' . $this->module . $pageNameFormatted;
        if (class_exists($className, true)) {
            $this->pageObject = new $className($this->driver, $args);
        } elseif (class_exists($classNameInModule, true)) {
            $this->pageObject = new $classNameInModule($this->driver, $args);
        }

        if ($this->pageObject === null) {
            $this->pageObject = new Page($this->driver, $args);
            $this->pageObject->setUri($pageName);
        }

        $this->pageName = $pageNameLower;
        return $this->pageObject;
    }

    public function page()
    {
        return $this->pageObject;
    }

    public function pageName()
    {
        return $this->pageName;
    }

    private function classStringFormatter($name)
    {
        $arrayFromString = array_map('ucfirst', explode('/', $name));
        $nameCaps = implode('', array_map('ucfirst', explode('-', end($arrayFromString))));
        $nameCaps = str_replace(' ', '', $nameCaps);
        $name = substr(implode('\\', $arrayFromString), 0, 0 - strlen(end($arrayFromString))) . $nameCaps;
        if (strpos($name, '\\') !== 0) {
            $name = '\\' . $name;
        }

        return $name;
    }
}
