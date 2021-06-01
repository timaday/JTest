@chrome
Feature: Jupiter Toys Shop
   As a Customer
   I should be able to add items to my cart
   So that can purchase products

   # 4.
   Scenario: Shopping cart - verify selected items in cart
   Given I am on the homepage
   When I navigate to the "Shop" page
   And I click the buy button for "Funny Cow"
   And I click the buy button for "Funny Cow"
   And I click the buy button for "Fluffy Bunny"
   And I click on the cart menu
   Then I see 2 items of "Funny Cow" in my cart
   And I see 1 item of "Fluffy Bunny" in my cart