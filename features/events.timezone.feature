Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-read   | apipass  | ROLE_API_READ  |
      | api-write  | apipass  | ROLE_API_WRITE |
      | api-write2 | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Create an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Event",
      "occurrences": [ {
        "startDate": "2000-01T00:00:00+00:00",
        "endDate": "2100-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.event.response.schema.json"
    And the JSON node "occurrences" should have 1 element
    And the JSON node "occurrences[0].startDate" should be equal to "2000-01-01T00:00:00+00:00"
    And the JSON node "occurrences[0].endDate" should be equal to "2100-01-01T00:00:00+00:00"

  Scenario: Create another event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Event",
      "occurrences": [ {
        "startDate": "2000-01T00:00:00+02:00",
        "endDate": "2100-01T00:00:00+02:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.event.response.schema.json"
    And the JSON node "occurrences" should have 1 element
    And the JSON node "occurrences[0].startDate" should be equal to "2000-01-01T00:00:00+02:00"
    And the JSON node "occurrences[0].endDate" should be equal to "2100-01-01T00:00:00+02:00"

  @dropSchema
  Scenario: Drop schema
