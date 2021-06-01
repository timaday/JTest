<?php

// composer autoload - includes psr-4 loading of TAS classes
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../../');
$dotenv->load();

// Define path to behat directory
define('BEHAT_FEATURE_ROOT', dirname(__DIR__));

// Define path for the page object model configuration - each module has is own configuration
define('BEHAT_PAGE_MODULE_CONFIGURATION', BEHAT_FEATURE_ROOT . '/{moduleName}_page_config.ini');

// Define path to application root
defined('ROOT_PATH') || define('ROOT_PATH', dirname(BEHAT_FEATURE_ROOT));

// define environment
defined('TEST_ENV_SECTION') || define(
    'TEST_ENV_SECTION',
    getenv('TEST_ENV_SECTION') ?: 'test'
);

$options = \TAS\Helper\ConfigurationManager::loadConfiguration(
    BEHAT_FEATURE_ROOT . '/config.ini',
    TEST_ENV_SECTION
);

function assignSetValue(&$value, $default = null) {
    return isset($value) ? $value : $default;
}
define('CHROME_SELENIUM_URL', getenv('CHROME_SELENIUM_URL') ?: 'selenium_chrome:4444/wd/hub');

define('TEST_BROWSER', assignSetValue($options['test']['driver']['selenium']['BROWSER'],'chrome'));
define('APP_URL', assignSetValue($options['test']['driver']['url'] ,null));

define('RELEASE_VERSION', getenv('RELEASE_VERSION') ?: 'DEVELOPMENT');