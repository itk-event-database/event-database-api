Feature: Daily Occurrences should not expose unpublished events
  In order to read daily occurrences
  As a client software developer
  I need to be able to retrieve daily occurrences through the API excluding 'unpublished'.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-read   | apipass  | ROLE_API_READ  |
      | api-write  | apipass  | ROLE_API_WRITE |
      | api-write2 | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Create an event with multiple occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Repeating event",
      "isPublished": false,
      "occurrences": [ {
        "startDate": "2011-01-02T13:00:00+00:00",
        "endDate": "2011-01-02T14:00:00+00:00",
        "place": {
          "name": "Some place"
        }
      },
      {
        "startDate": "2012-01-02T13:00:00+00:00",
        "endDate": "2012-01-04T14:00:00+00:00",
        "place": {
          "name": "Another place"
        }
      } ]
    }
    """
    Then the response status code should be 201

  Scenario: Count DailyOccurrences
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?startDate[after]=@0&endDate[after]=@0"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Count Occurrences
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences?startDate[after]=@0&endDate[after]=@0"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  @dropSchema
  Scenario: Drop schema
