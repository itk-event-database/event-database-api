Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Create Events
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The past event",
      "occurrences": [ {
        "startDate": "2000-01-01",
        "endDate": "2001-01-01"
      } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/1"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The future event",
      "occurrences": [ {
        "startDate": "2100-01-01",
        "endDate": "2101-01-01"
      } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Get events
    When I send a "GET" request to "/api/events"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Get future events
    When I send a "GET" request to "/api/events?occurrences.startDate[after]=2050-01-01"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Get past events
    When I send a "GET" request to "/api/events?occurrences.startDate[before]=2050-01-01&occurrences.endDate[after]=@0"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

  Scenario: Get all events
    When I send a "GET" request to "/api/events?occurrences.startDate[after]=1900-01-01&occurrences.endDate[after]=1900-01-01"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

  @dropSchema
  Scenario: Drop schema
