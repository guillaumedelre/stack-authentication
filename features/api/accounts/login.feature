@api
Feature: Account resource login

    Scenario: Setup feature
        Given the database is clear
        Given the fixtures for group "api_accounts" are loaded

    Scenario: User test login
        Given I add "Content-Type" header equal to "application/json"
        Then I send a "POST" request to "/token" with body:
        """
        {
            "email": "test@stack.local",
            "password": "test"
        }
        """
        And the response should be in JSON
        And the response status code should be 200
        And the JSON node "token" should be a valid JWT
