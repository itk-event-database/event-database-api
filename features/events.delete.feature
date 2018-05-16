Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Get deleted events
    When I send a "GET" request to "/api/events/deleted.json"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "" should have 0 elements

  Scenario: Create an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Big bang",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/events/1"

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Big bang",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Delete an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 204
    And the response should be empty

  Scenario: Get deleted events
    When I send a "GET" request to "/api/events/deleted.json"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "" should have 1 element
    And the JSON node "[0].id" should be equal to "1"

  Scenario: Delete an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 204
    And the response should be empty

  Scenario: Get deleted events
    When I send a "GET" request to "/api/events/deleted.json"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "" should have 2 elements
    And the JSON node "[0].id" should be equal to "1"
    And the JSON node "[1].id" should be equal to "2"

  @dropSchema
  Scenario: Drop schema
