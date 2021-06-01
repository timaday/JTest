<?php

namespace TAS\Definition\ContextSupport;

use TAS\Definition\PageObject\PageObjectManager;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use TAS\Adaption\Browser\CustomWebDriver;
use TAS\Definition\PageObject\PageObjectSupport;

abstract class ContextDefinition implements Context
{
    protected static $pageObjectFactory;
    /** @var PageObjectSupport */
    protected $page;

    /** @var \Behat\Mink\Session */
    protected static $session;

    /** @var CustomWebDriver */
    protected static $driver;
    protected static $browser;
    protected static $baseUrl;

    public function __construct($module = 'Undefined', $browser = "chrome", $baseUrl = null)
    {
        self::$pageObjectFactory = new PageObjectManager($module);
        self::$browser = $browser;
        self::$baseUrl = $baseUrl ?: APP_URL;
    }

    /*
   * BEFORE & AFTER SECTION
   */

    /**
     * @BeforeScenario
     */
    public function setUp(BeforeScenarioScope $event)
    {
        $this->cleanDirectories();
        self::$driver = new CustomWebDriver(self::$browser, self::$baseUrl);
    }

    /**
     * @AfterScenario
     */
    public function tearDown(AfterScenarioScope $event)
    {
        $this->cleanDirectories();
        if (self::$driver instanceof CustomWebDriver) {
            self::$driver->quitDriver();
        }
    }

    public function cleanDirectories()
    {
        // ignores all hidden files, example: .gitkeep
        $directoryContent = glob(BEHAT_FEATURE_ROOT . '/{Downloads,Logs}/*', GLOB_BRACE);
        if (!empty($directoryContent)) {
            array_map('unlink', array_filter($directoryContent));
        }
    }

    /*
   * BEFORE & AFTER SECTION END
   */

    /**
     * Load page Object
     * @param string|null $pageUri
     * @param array $args
     * @return PageObjectSupport
     */
    protected function getPageObject($pageUri = null, $args = []) : PageObjectSupport
    {
        return $this->page = self::$pageObjectFactory->getPage($pageUri, self::$driver, $args);
    }
}