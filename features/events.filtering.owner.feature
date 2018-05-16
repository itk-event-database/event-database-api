Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username | roles          | groups           |
      | user-0   | ROLE_API_WRITE | group-0          |
      | user-1   | ROLE_API_WRITE | group-0          |
      | user-2   | ROLE_API_WRITE | group-0, group-1 |
      | user-3   | ROLE_API_WRITE | group-1          |

  @createSchema
  Scenario: Create Events
    When I authenticate as "user-0"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Event (user-0)",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/1"

    When I authenticate as "user-1"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Event (user-1)",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Filter by user
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0&user=user-0"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0&user=user-1"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&user=user-2"
    Then the JSON node "hydra:member" should have 0 elements

  Scenario: Filter by group
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0&group=group-0"
    Then the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0&group=group-1"
    Then the JSON node "hydra:member" should have 0 elements

  Scenario: Create Events
    When I authenticate as "user-2"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Event (user-2)",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/3"

  Scenario: Filter by group
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0&group=group-0"
    Then the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0&group=group-1"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/3"

  Scenario: Filter by editable_by
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0&editable_by=user-3"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/3"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&editable_by=user-4"
    Then the JSON node "hydra:member" should have 0 elements

  @dropSchema
  Scenario: Drop schema
