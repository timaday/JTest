<?php

namespace TAS\Definition;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\Assert;
use TAS\Definition\ContextSupport\ContextDefinition;
use TAS\Definition\Exceptions\ExpectationException;
use TAS\Definition\PageObject\PageObjectSupport;

/**
 * Defines application features from the specific context.
 */
class JupiterDefaultContext extends ContextDefinition implements Context
{

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct($module = 'Jupiter', $browser = 'chrome', $baseUrl = null)
    {
        parent::__construct($module, $browser, $baseUrl ?: APP_URL);
    }

    /**
     * @Given I am on the homepage
     */
    public function iAmOnTheHomepage()
    {
        self::$driver->getURL('/');
        self::$driver->driver()->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName('body'))
        );
    }
    /**
     * @When I navigate to the :arg1 page
     */
    public function iNavigateToThePage($pageName)
    {
        $this->getPageObject($pageName);
        if ($this->page instanceof PageObjectSupport) {
            $this->page->go();
        }
    }

    /**
     * @When I click :arg1
     */
    public function iClick($arg1)
    {
        $this->page->clickElement($arg1, [], 1, true);
    }

    /**
     * @Then I see the following error messages
     */
    public function iSeeTheFollowingErrorMessages(TableNode $table)
    {
        foreach($table->getHash() as $row) {
            if (!isset($row['FieldName']) || !isset($row['Message'])) {
                throw new \InvalidArgumentException('FieldName or Message column is missing from step table');
            }
            $fieldName = $row['FieldName'];
            $expectedError = $row['Message'];
            $element = $this->page->getPageElement($fieldName."FieldError", [], 2, true);
            $actualError = $element ? $element->getText() : null;
            Assert::assertSame($expectedError, $actualError, "Incorrect Error message on page.
            \n Expected: {$expectedError} 
            \n Actual: {$actualError}"
            );
        }
    }

    /**
     * @Then I should not see any error for the following fields
     */
    public function iShouldNotSeeAnyErrorForTheFollowingFields(TableNode $table)
    {
        foreach($table->getHash() as $row) {
            if (!isset($row['FieldName'])) {
                throw new \InvalidArgumentException('FieldName column is missing from step table');
            }
            $fieldName = $row['FieldName'];
            $element = $this->page->getPageElement($fieldName . "FieldError", [], 2, true);
            if ($element instanceof WebDriverElement && $element->isDisplayed()) {
                throw new ExpectationException("Error message found for $fieldName field");
            }
        }
    }

    /**
     * @When I enter :value into :LogicalName
     */
    public function iEnterInto($value, $logicalName)
    {
        $this->page->enterText($logicalName, $value, [], 1, true);
    }

    /**
     * @Then I see :logicalName with following text
     */
    public function iSeeWithFollowingText($logicalName, PyStringNode $string)
    {
        $actualText = $this->page->getText($logicalName, [], 20, true);
        $expectedText = $string->getRaw();
        Assert::assertSame($expectedText, $actualText, "Message does not match: \n Expected: '$expectedText' \n Actual: '$actualText'");
    }

    /**
     * @Then I should not see :logicalName with following text
     */
    public function iShouldNotSeeWithFollowingText($logicalName, PyStringNode $string)
    {
        $actualText = $this->page->getText($logicalName, [], 20, true);
        $expectedText = $string->getRaw();
        Assert::assertNotSame($expectedText, $actualText, "Message matches given text: \n Expected: '$expectedText' \n Actual: '$actualText'");
    }

    /**
     * @Then I should not see :logicalName
     */
    public function iShouldNotSee($logicalName)
    {
        $actualText = $this->page->getPageElement($logicalName);
        if($actualText instanceof WebDriverElement && $actualText->isDisplayed()) {
            throw new ExpectationException("Element found on page: $logicalName");
        }
    }


    /**
     *
     * @When I click the buy button for :productName
     */
    public function iClickTheBuyButtonFor($productName)
    {
        $this->page->clickElement('BuyProduct', ["productName"=> $productName], 5);
    }

    /**
     * @When I click on the cart menu
     */
    public function iClickOnTheCartMenu()
    {
        $this->page->clickElement('CartMenu');
        $this->getPageObject('Cart');
    }

    /**
     * @Then I see :expectedQuantity items of :productName in my cart
     * @Then I see :expectedQuantity item of :productName in my cart
     */
    public function iSeeItemsOfInMyCart($expectedQuantity, $productName)
    {
        $actualQuantity = $this->page->getFieldValue("QuantityOfItem", ["productName"=> $productName]);
        Assert::assertSame($expectedQuantity, $actualQuantity,
            "Quantity does not match\n Expected: {$expectedQuantity} \n Actual: {$actualQuantity}"
        );
    }
}
