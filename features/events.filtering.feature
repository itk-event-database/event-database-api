Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Create Events
    When I authenticate as "api-write"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The first event",
      "occurrences": [ {
        "startDate": "2000-01-01"
      }, {
        "startDate": "2100-01-01"
      } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/1"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The second event",
      "occurrences": [ {
        "startDate": "2010-01-01"
      }, {
        "startDate": "2110-01-01"
      } ]

    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Filter by name
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events?name=first"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I send a "GET" request to "/api/events?name=second"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Filter by startDate (before)
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events?occurrences.startDate[before]=2001-01-01"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

  Scenario: Filter by startDate (after)
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events?occurrences.startDate[after]=2101-01-01"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Filter by startDate with timezone (after) (awaits merge of https://github.com/api-platform/core/pull/672)
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events?occurrences.startDate[after]=2101-01-01T00:00:00+02:00"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  Scenario: Sort by startDate ascending
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events?order[occurrences.startDate]=asc"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

  Scenario: Sort by startDate descending
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events?order[occurrences.startDate]=desc"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/1"

  @dropSchema
  Scenario: Drop schema
