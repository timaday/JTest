@chrome
Feature: Jupiter Toys Contact Page
  As a Customer
  The feedback form provide relevant validation messages
  So that can provide feedback in the correct format

  ### 1.
  Scenario: Contact form validation - correct errors
    Given I am on the homepage
    When I navigate to the "Contact" page
    And I click "SubmitButton"
    Then I should not see "SuccessMessage"
    And I see the following error messages
     | FieldName     | Message              |
     | Forename      | Forename is required |
     | Email         | Email is required    |
     | Message       | Message is required  |
    And I should not see any error for the following fields
      | FieldName |
      | Surname   |
      | Telephone |
    When I enter "John" into "ForenameField"
    And I enter "john@test.com" into "EmailField"
    And I enter "This is my feedback" into "MessageField"
    Then I should not see any error for the following fields
      | FieldName |
      | Surname   |
      | Telephone |
      | Forename  |
      | Email     |
      | Message   |


### 2.
  Scenario: Contact form validation - provide all required fields and submit
    Given I am on the homepage
    When I navigate to the "Contact" page
    When I enter "John" into "ForenameField"
    And I enter "john@test.com" into "EmailField"
    And I enter "This is my feedback" into "MessageField"
    And I click "SubmitButton"
    Then I see "SuccessMessage" with following text
    """
    Thanks John, we appreciate your feedback.
    """


    ### 3.1
    ## Only mandatory email field validates format
  Scenario: Contact form validation - mandatory field validation
    Given I am on the homepage
    When I navigate to the "Contact" page
    When I enter "  " into "ForenameField"
    And I enter "sdfsdfdsf" into "EmailField"
    And I enter "   " into "MessageField"
    And I click "SubmitButton"
    Then I should not see "SuccessMessage"
    And I see the following error messages
      | FieldName     | Message                      |
      | Forename      | Forename is required         |
      | Email         | Please enter a valid email   |
      | Message       | Message is required          |

   # 3.2 - extra
  Scenario: Contact form validation - field validation
    Given I am on the homepage
    When I navigate to the "Contact" page
    And I enter "sdfsdfdsf" into "EmailField"
    And I enter "+44 test test" into "TelephoneField"
    And I click "SubmitButton"
    Then I should not see "SuccessMessage"
    And I see the following error messages
      | FieldName     | Message                                       |
      | Forename      | Forename is required                          |
      | Email         | Please enter a valid email                    |
      | Telephone     | Please enter a valid telephone number         |
      | Message       | Message is required                           |


