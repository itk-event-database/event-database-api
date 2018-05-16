Feature: Occurrences
  In order to manage occurrences
  As a client software developer
  I need to be able to retrieve, create, update and delete occurrences trough the API.

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

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
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

  Scenario: Get occurrences
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences"
    And print last JSON response
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/2"

  Scenario: Get future occurrences
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences?startDate[after]=2050-01-01"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/2"

  Scenario: Get past occurrences
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences?startDate[before]=2050-01-01&endDate[after]=@0"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"

  Scenario: Get all occurrences
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences?startDate[after]=1900-01-01&endDate[after]=1900-01-01"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/occurrences/2"

  @dropSchema
  Scenario: Drop schema
