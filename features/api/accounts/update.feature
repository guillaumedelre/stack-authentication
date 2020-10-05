@api
Feature: Account resource update

    Scenario: Setup feature
        Given the database is clear
        Given the fixtures for group "api_accounts" are loaded
        Given a bearer is generated for "admin@stack.local"
        And I save it into "BEARER"

    Scenario: Update an account
        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Then I send a "GET" request to "/api/accounts.jsonld"
        And the response should be in JSON
        And the response status code should be 200

        Given the JSON node "hydra:member[0].@id"
        And I save it into "FIRST_USER_IRI"
        Given the JSON node "hydra:member[0].email"
        And I save it into "FIRST_USER_EMAIL"
        Given the JSON node "hydra:member[0].fullname"
        And I save it into "FIRST_USER_FULLNAME"

        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Given I add "Content-Type" header equal to "application/json"
        Then I send a "PUT" request to "<<FIRST_USER_IRI>>.jsonld" with body:
        """
        {
            "fullname": "fuel name",
            "plainPassword": "new_password"
        }
        """
        And the response should be in JSON
        And the response status code should be 200
        And the JSON node "@type" should be equal to the string "Account"
        And the JSON node "fullname" should not contain "<<FIRST_USER_FULLNAME>>"

        Given I add "Content-Type" header equal to "application/json"
        Then I send a "POST" request to "/token" with body:
        """
        {
            "email": "<<FIRST_USER_EMAIL>>",
            "password": "new_password"
        }
        """
        And the response should be in JSON
        And the response status code should be 200
        And the JSON node "token" should be a valid JWT
