@api
Feature: Account resource creation

    Scenario: Setup feature
        Given the database is clear
        Given the fixtures for group "api_accounts" are loaded
        Given a bearer is generated for "admin@stack.local"
        And I save it into "BEARER"

    Scenario: Create an account
        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Given I add "Content-Type" header equal to "application/json"
        Then I send a "POST" request to "/api/accounts.jsonld" with body:
        """
        {
            "fullname": "john doe",
            "email": "johndoe@gmail.com",
            "plainPassword": "johndoe",
            "roles": ["ROLE_USER"]
        }
        """
        And the response should be in JSON
        And the response status code should be 201
        And the JSON node "@type" should be equal to the string "Account"
        And the JSON node "fullname" should be equal to the string "john doe"
        And the JSON node "email" should be equal to the string "johndoe@gmail.com"
        And the JSON node "roles[0]" should be equal to the string "ROLE_USER"

    Scenario: Validation errors
        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Given I add "Content-Type" header equal to "application/json"
        Then I send a "POST" request to "/api/accounts.jsonld" with body:
        """
        {}
        """
        And the response should be in JSON
        And the response status code should be 400
        And the JSON node "violations" should have 1 element
        And the JSON node "violations[0].propertyPath" should be equal to the string "email"
        And the JSON node "violations[0].message" should be equal to the string "This value should not be blank."
