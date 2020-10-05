@api
Feature: Account resource deletion

    Scenario: Setup feature
        Given the database is clear
        Given the fixtures for group "api_accounts" are loaded
        Given a bearer is generated for "admin@stack.local"
        And I save it into "BEARER"

    Scenario: Delete an item
        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Then I send a "GET" request to "/api/accounts.jsonld"
        And the response should be in JSON
        And the response status code should be 200
        Given the JSON node "hydra:member[0].@id"
        And I save it into "FIRST_USER"
        Given the JSON node "hydra:member[1].@id"
        And I save it into "SECOND_USER"
        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Then I send a "DELETE" request to "<<FIRST_USER>>.jsonld"
        And the response status code should be 204

    Scenario Outline: Delete with invalid authorization header
        Given I add "Authorization" header equal to "<authorization>"
        Given I send a "DELETE" request to "<path>"
        And the response should be in JSON
        And the response status code should be <status>

        Examples:
            | authorization | path                  | status |
            | ~             | <<SECOND_USER>>.jsonld | 401    |
            | <<BEARER>>    | <<SECOND_USER>>.jsonld | 401    |
