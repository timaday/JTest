<?php

namespace TAS\Adaption\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use TAS\Definition\Exceptions\NoSuchElementException;
use PHPUnit\Framework\Assert;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class CustomWebDriver
{
    /**
     * @var WebDriver | bool
     */
    protected $driver = false;

    protected $mainHandle = false;

    protected $baseUrl;

    public function __construct($browser = 'chrome', $baseUrl = null)
    {
        $this->setBaseUrl($baseUrl ?: APP_URL);
        $this->createDriver($browser);
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    private  function createChromeDriver()
    {
        $options = new ChromeOptions();
        $options->addArguments(['--whitelisted-ips=']);
        $options->setExperimentalOption('w3c', false);
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        $this->driver = RemoteWebDriver::create(CHROME_SELENIUM_URL, $caps);
    }


    private function createDriver($browser)
    {
        switch ($browser) {
            case "chrome":
            default:
                $this->createChromeDriver();
                break;
        }
    }

    public function driver()
    {
        return $this->driver;
    }

    public function quitDriver()
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }

    public function getLocatorStrategy($selector, $query)
    {
        switch(strtolower($selector)) {
            case 'id':
                return WebDriverBy::id($query);
            case 'class':
            case 'classname':
                return WebDriverBy::className($query);
            case 'name':
                return WebDriverBy::name($query);
            case 'tag':
            case 'tagname':
                return WebDriverBy::tagName($query);
            case 'link':
            case 'linktext':
                return WebDriverBy::linkText($query);
            case 'patiallinktext':
                return WebDriverBy::partialLinkText($query);
            case 'css':
            case 'cssSelector':
                return WebDriverBy::cssSelector($query);
            case 'xpath':
            default:
                return WebDriverBy::xpath($query);
        }
    }

    private function waitForElement(WebDriverBy $webDriverBy, $wait = 5) {
        if ($wait > 0) {
            $this->driver->wait($wait, 50)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated($webDriverBy)
            );
        }
    }

    Private function scrollToElement($element)
    {
        if ($element instanceof WebDriverElement) {
            $element->getLocationOnScreenOnceScrolledIntoView();
        }
    }

    public function element($selector, $query, $wait = 5, $scroll = false)
    {
        $webdriverBy = $this->getLocatorStrategy($selector, $query);
        try {
            $this->waitForElement($webdriverBy, $wait);
            $element = $this->driver->findElement($webdriverBy);
        } catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
            return null;
        }
        if ($scroll) {
            $this->scrollToElement($element);
        }
        return $element;
    }


    public function elements($selector, $query, $wait = 0)
    {
        $webdriverBy = $this->getLocatorStrategy($selector, $query);
        $this->waitForElement($webdriverBy, $wait);
        try {
            return $this->driver->findElements($webdriverBy);
        } catch (NoSuchElementException $e) {
            return [];
        }
    }

    public function elementExists($selector, $query)
    {
        $element = $this->element($selector, $query);
        return !empty($element);
    }

    public function getURL($uri = false, $baseUrl = null)
    {
        try {
            $this->driver->navigate()->to($this->locatePath($uri, $baseUrl));
            // set as main window
            $this->mainHandle = $this->driver()->getWindowHandle();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function locatePath($url, $baseUrl = null)
    {
        $url = $url ? $url : '';
        if (strpos($url, 'http://') === 0 && is_int(strpos($url, 'http://'))
            || strpos($url, 'https://') === 0 && is_int(strpos($url, 'https://'))
            || strpos($url, 'file://') === 0 && is_int(strpos($url, 'file://'))
        ) {
            return $url;
        }
        // Ensure uri starts with forward slash
        $url = preg_match('/^(\/)/', $url, $matches) ? $url : '/' . $url;
        if (!$this->baseUrl) {
            $this->setBaseUrl($baseUrl ?: APP_URL);
        }
        return $this->baseUrl . $url;
    }

    public function findText($text)
    {
        $pageSource = $this->driver->getPageSource();
        return strpos($pageSource, $text) !== false;
    }

    public function takeSnapshot($name = 'snapshot')
    {
        $snapshotName = 'features/screenshot/' . date('Y-m-d H:i:s') . ' - ' . $name . '.jpg';
        $this->driver->takeScreenshot($snapshotName);
    }

    public function checkTitle($expectedTitle)
    {
        $element = $this->element('css', 'head title');
        if (!$element) {
            throw new NoSuchElementException(
                "Unable to access Pages <title> - Please ensure environment is setup correctly'"
            );
        }
        $actualTitle = trim($element->getAttribute('innerHTML'));
        Assert::assertSame(
            $actualTitle,
            $expectedTitle,
            "Pages <title> does not match expected: '{$expectedTitle}'"
        );
    }

    /**
     * @param $selector
     * @param $query
     * @return WebDriverElement|null
     */
    public function click($selector, $query)
    {
        $element = $this->element($selector, $query);
        if (!$element) {
            return null;
        }
        $element->click();

        return $element;
    }

    /**
     * @param $selector
     * @param $query
     * @param $fieldName
     * @param $text
     * @param int $wait
     * @param bool $scroll
     * @return WebDriverElement|null
     */
    public function type($selector, $query, $text, $wait = 5, $scroll = true)
    {
        $element = $this->element($selector, $query, $wait, $scroll);
        if (!$element) {
            return null;
        }
        $element->sendKeys($text);
        return $element;
    }

    public function maximizeCurrentWindow()
    {
        $this->driver->manage()->window()->maximize();
    }

    public function getCookies()
    {
        return $this->driver->manage()->getCookies();
    }
}
