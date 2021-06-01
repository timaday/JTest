<?php

namespace TAS\Definition\PageObject;

use Facebook\WebDriver\WebDriverElement;
use TAS\Adaption\Browser\CustomWebDriver;
use TAS\Definition\Exceptions\NoSuchElementException;

class PageObjectSupport extends PageConfigurationManager
{
    protected $uri = "/";
    protected $pageName = 'pages';
    protected $elements = [];
    protected $args = [];
    protected $supportedLocators = ['xpath', 'css', 'id'];

    public function __construct($customDriver, array $args = [])
    {
        $this->args = $args;
        $this->loadConfiguration($this->pageName, $this->args);
        if ($customDriver instanceof CustomWebDriver) {
            $this->driver = $customDriver->driver();
        } else {
            throw new \RuntimeException("Unable to load PageObject: {$this->pageName}");
        }
    }

    public function setUri($uri)
    {
        if ($this->uri) {
            $this->uri = $uri;
        }
        if ($this->pageName === 'pages' && $this->uri !== '/') {
            // If profile exists in ini file load it otherwise use pages as default.
            try {
                $pageName = preg_replace("/^(\/)/", '', strtolower($this->uri));
                $pageName = str_replace('/', '_', $pageName);
                $this->loadConfiguration($pageName, $this->args);
                $this->pageName = $pageName;
            } catch (\Exception $e){};
        }
    }

    public function go()
    {
        if (!$this->getURL($this->uri)) {
            throw new \RuntimeException("Unable to navigate to $this->uri");
        }
        $this->maximizeCurrentWindow();
    }

    protected function getLocatorByLogicalName($logicalName)
    {
        $errorMessage = "Unable to find logical name: '$logicalName'. Please check configuration.";
        if (!isset($this->elements[$logicalName])) {
            throw new \RuntimeException($errorMessage);
        }
       $locator = $this->elements[$logicalName];
        if (!is_array($locator)) { // Default locator strategy is xpath
            return ["selector" => "xpath", "query" => $locator];
        }
        if (isset($locator['query']) && isset($locator['selector'])) { // Custom locator strategy
            return $locator;
        }
        foreach ($locator as $selector => $query) { // Specific
            if(!in_array($selector, $this->supportedLocators)){
                continue;
            }
            $locator['selector'] = $selector;
            $locator['query'] = $query;
            return $locator;
        }
        throw new \RuntimeException($errorMessage);
    }

    protected function getLocatorByLogicalNameAndParseArgs($logicalName, $args = [])
    {
        $locator = $this->getLocatorByLogicalName($logicalName);
        $locator['query'] = $this->replaceStringWithArgs($locator['query'], $args);
        return $locator;
    }

    protected function getFieldLocatorByLogicalNameAndParseArgs($logicalName, $args = [])
    {
        $locator = $this->getLocatorByLogicalName($logicalName);
        if (!isset($locator['fieldName'])) {
            throw new \InvalidArgumentException(
                "Unable to find fieldName for  locator: '$logicalName' in ini file."
            );
        }
        return $locator;
    }

    /**
     * @param string $logicalName
     * @param array $args
     * @param int $wait
     * @param bool $scroll
     * @return WebDriverElement|null
     */
    public function getPageElement($logicalName, $args = [], $wait = 0, $scroll = false)
    {
        $locator = $this->getLocatorByLogicalNameAndParseArgs($logicalName, $args);
        return $this->element($locator['selector'], $locator['query'], $wait, $scroll);
    }

    /**
     * @param string $logicalName
     * @param array $args
     * @param int $wait
     * @param bool $scroll
     * @return WebDriverElement[]|null
     */
    public function getPageElements($logicalName, $args = [], $wait = 0)
    {
        $locator = $this->getLocatorByLogicalNameAndParseArgs($logicalName, $args);
        return $this->elements($locator['selector'], $locator['query'], $wait);
    }

    public function getTitle()
    {
        $title = $this->getPageElement('pageHeader', [], 0, false);
        if (!$title) {
            throw new \RuntimeException('Unable to access the page title');
        }
        return $title->getText();
    }

    public function getPageHeader()
    {
        return $this->getPageElement('pageHeader', [], 0, false);
    }

    public function selectOption($logicalName, $option, $args = [], $value = true)
    {
        $locator = $this->getLocatorByLogicalNameAndParseArgs($logicalName, $args);
        return $this->select($locator['selector'], $locator['query'], $option, $value);
    }

    public function setCheckbox($logicalName, $checked = true, $args = [])
    {
        $checkbox = $this->getPageElement($logicalName, $args);
        if (!empty($checkbox) && $checked === true && !$checkbox->isChecked()) {
            $checkbox->check();
        }
        if (!empty($checkbox) && $checked === false && $checkbox->isChecked()) {
            $checkbox->uncheck();
        }
    }

    public function getText($logicalName, $args = [], $wait = 0, $scroll = true)
    {
        $element = $this->getPageElement($logicalName, $args,  $wait, $scroll);
        return $element
            ? $element->getText()
            : null;
    }

    public function clickElement($logicalName, $args = [], $wait = 0, $scroll = true)
    {
        $element = $this->getPageElement($logicalName, $args, $wait, $scroll);
        if ($element) {
            $element->click();
        }

        return (bool) $element;
    }

    /**
     * @param $logicalName
     * @param $text
     * @param array $args
     * @param int $wait
     * @param bool $scroll
     * @return WebDriverElement|null
     */
    public function enterText($logicalName, $text, $args = [], $wait = 0, $scroll = true)
    {
        $field = $this->getPageElement($logicalName, $args, $wait, $scroll);
        if (!$field instanceof WebDriverElement) {
            throw new NoSuchElementException("Unable to find element: '$logicalName'");
        }
        $field->clear();

        $field->sendKeys($text);

        return $field;
    }

    public function getFieldValue($logicalName, $args = [], $wait = 0, $scroll = true)
    {
       $field = $this->getPageElement($logicalName, $args, $wait, $scroll);
       return  $field ? $field->getAttribute('value') : null;
    }

    public function replaceStringWithArgs($string, array $args = [])
    {
        $parsedString = $string;
        foreach ($args as $key => $value) {
            $parsedString = str_replace('{' . $key . '}', $value, $parsedString);
        }
        preg_match_all('/({)([a-zA-Z0-9])*(})/', $parsedString, $match);
        $remainingVariables = array_unique(current($match));
        if (count((array) $remainingVariables) > 0) {
            throw new \InvalidArgumentException(
                'Configuration Error - Unassigned variable name/s: ' . implode(', ', $remainingVariables)
                . PHP_EOL . 'String was: "' . $string .'"'
            );
        }
        return $parsedString;
    }

    /**
     * @param $logicalName
     * @param array $args
     * @param int $wait
     * @param bool $scroll
     * @return WebDriverElement|null
     */
    public function getElementWithArgs($logicalName, $args = [], $wait = 5, $scroll = true)
    {
        $locator = $this->getLocatorByLogicalNameAndParseArgs($logicalName, $args);
        $wait *= 5;
        $element = null;
        for ($i = 0; $i <= $wait;$i++) {
            $element = $this->element($locator['selector'], $locator['selector'], $wait, $scroll);
            if (!empty($element)) {
                break;
            }
            usleep(200000);
        }
        return $element;
    }
}