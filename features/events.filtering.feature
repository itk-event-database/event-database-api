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
  Scenario: Create Events
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The first event",
      "langcode": "en",
      "occurrences": [ {
        "startDate": "2000-01-01",
        "place": {
          "name": "Place 1"
        }
      }, {
        "startDate": "2100-01-01"
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
      "name": "The second event",
      "langcode": "da",
      "occurrences": [ {
        "startDate": "2010-01-01",
        "place": {
          "name": "Place 2"
        }
      }, {
        "startDate": "2110-01-01"
      } ]

    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Filter by name
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?name=first"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?name=second"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Filter by langcode
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?langcode=en"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?langcode=da"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Filter by startDate (before)
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[before]=2001-01-01"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

  Scenario: Filter by startDate (after)
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=2101-01-01"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Filter by startDate with timezone (after) (awaits merge of https://github.com/api-platform/core/pull/672)
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=2101-01-01T00:00:00%2B02:00"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Sort by startDate ascending
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?order[occurrences.startDate]=asc"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

  Scenario: Sort by startDate descending
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&order[occurrences.startDate]=desc"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/1"

  Scenario: Filter by place name
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.place.name=Place 1"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

  Scenario: Filter by place name
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.place.name=Place 2"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  @dropSchema
  Scenario: Drop schema
