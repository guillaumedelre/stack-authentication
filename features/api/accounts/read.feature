@api
Feature: Account resource reading

    Scenario: Setup feature
        Given the database is clear
        Given the fixtures for group "api_accounts" are loaded
        Given a bearer is generated for "admin@stack.local"
        And I save it into "BEARER"

    Scenario: Read a collection
        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Then I send a "GET" request to "/api/accounts.jsonld"
        And the response should be in JSON
        And the response status code should be 200
        And the JSON should be valid according to the schema "features/api/accounts/collectionOperation.schema"
        Given the JSON node "hydra:member[0].@id"
        And I save it into "FIRST_USER"
        And the JSON node "hydra:totalItems" should be equal to the number 2

    Scenario: Read an item
        Given I add "Authorization" header equal to "Bearer <<BEARER>>"
        Then I send a "GET" request to "<<FIRST_USER>>.jsonld"
        And the response should be in JSON
        And the response status code should be 200
        And the JSON should be valid according to the schema "features/api/accounts/itemOperation.schema"

    Scenario Outline: Read with invalid authorization header
        Given I add "Authorization" header equal to "<authorization>"
        Given I send a "GET" request to "<path>"
        And the response should be in JSON
        And the response status code should be <status>

        Examples:
            | authorization | path                  | status |
            | ~             | /api/accounts.jsonld  | 401    |
            | ~             | <<FIRST_USER>>.jsonld | 401    |
            | <<BEARER>>    | /api/accounts.jsonld  | 401    |
            | <<BEARER>>    | <<FIRST_USER>>.jsonld | 401    |

