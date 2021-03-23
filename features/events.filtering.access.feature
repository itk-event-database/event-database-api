Feature: Events
    In order to manage events
    As a client software developer
    I need to be able to retrieve, create, update and delete events trough the API.

    Background:
        Given the following users exist:
            | username   | password | roles          |
            | api-write  | apipass  | ROLE_API_WRITE |

    @createSchema
    Scenario: Create Full/Limited Events
        When I authenticate as "api-write"
        And I add "Content-Type" header equal to "application/ld+json"
        And I add "Accept" header equal to "application/ld+json"
        And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The full access event",
      "hasFullAccess": true,
      "occurrences": [ {
        "startDate": "2100-01-01",
        "endDate": "2100-01-02"
      } ]
    }
    """
        Then the response status code should be 201
        And the JSON node "@id" should be equal to "/api/events/1"

        When I authenticate as "api-write"
        And I add "Content-Type" header equal to "application/ld+json"
        And I add "Accept" header equal to "application/ld+json"
        And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The limited access event",
      "hasFullAccess": false,
      "occurrences": [ {
        "startDate": "2100-01-01",
        "endDate": "2100-01-02"
      } ]
    }
    """
        Then the response status code should be 201
        And the JSON node "@id" should be equal to "/api/events/2"

    Scenario: Get events should only return the full access event
        When I add "Content-Type" header equal to "application/ld+json"
        And I add "Accept" header equal to "application/ld+json"
        And I send a "GET" request to "/api/events"
        And the JSON node "hydra:member" should have 1 element
        And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    Scenario: All: Get events should return both events if queried for 'all'
        When I add "Content-Type" header equal to "application/ld+json"
        And I add "Accept" header equal to "application/ld+json"
        And I send a "GET" request to "/api/events?access=all"
        And the JSON node "hydra:member" should have 2 element
        And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
        And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

    Scenario: Limited: Get events should only return limited access events if queried for 'limited'
        When I add "Content-Type" header equal to "application/ld+json"
        And I add "Accept" header equal to "application/ld+json"
        And I send a "GET" request to "/api/events?access=limited"
        And the JSON node "hydra:member" should have 1 element
        And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

    Scenario: Full: Get events should only return full access events if queried for 'full'
        When I add "Content-Type" header equal to "application/ld+json"
        And I add "Accept" header equal to "application/ld+json"
        And I send a "GET" request to "/api/events?access=full"
        And the JSON node "hydra:member" should have 1 element
        And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    @dropSchema
    Scenario: Drop schema
