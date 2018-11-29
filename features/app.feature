Feature:
  In order to get the OFX statement for the Redsys gateway
  Having downloaded the Sabadell and Redsys statements
  As an anonymous user

  Scenario: It shows the form in the home
    When I go to the homepage
    Then I should see "Redsys statement to OFX"
    And I should see 3 "input" elements
    And I should see "Commerce id"
    And I should see "Consignment Statement (.xls)"
    And I should see "Redsys transactions (.csv)"
    And I should see a "button[type=submit]" element

  Scenario: It validates the mandatory inputs
    Given I am on the homepage
    When I press "Submit"
    Then I should see 3 ".has-error" elements

  Scenario: It checks that the file is an Excel file
    Given I am on the homepage
    When I attach the file "blank.txt" to "consignmentStatement"
    And I press "Submit"
    Then I should see "The mime type of the file is invalid"

  Scenario: It creates an OFX statement when sending a valid file
    Given I am on the homepage
    When I fill in "commerceId" with "123456789"
    And I attach the file "sabadell-consignment.xls" to "consignmentStatement"
    And I attach the file "redsys-statements.csv" to "transactionStatement"
    And I press "Submit"
    Then the response status code should be 200
    And the response should contain "OFXHEADER:100"
    And the response should contain "<ACCTID>123456789</ACCTID>"
    And the response should contain "<MEMO>FR-3706 (36.38 GBP)</MEMO>"
    And the response should contain "<NAME>ABC10</NAME>"
    And the response is loadable on python

  Scenario: It shows the missing column in the statement
    Given I am on the homepage
    When I fill in "commerceId" with "123456789"
    And I attach the file "sabadell-consignment.xls" to "consignmentStatement"
    And I attach the file "redsys-statements-no-date.csv" to "transactionStatement"
    And I press "Submit"
    Then I should see "Fecha" in the ".alert" element
