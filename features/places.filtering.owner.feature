Feature: Places
  In order to manage places
  As a client software developer
  I need to be able to retrieve, create, update and delete places trough the API.

  Background:
    Given the following users exist:
      | username | roles          | groups  |
      | user-0   | ROLE_API_WRITE | group-0 |
      | user-1   | ROLE_API_WRITE | group-0 |
      | user-2   | ROLE_API_WRITE | group-0, group-1  |
      | user-3   | ROLE_API_WRITE | group-1 |

  @createSchema
  Scenario: Create Places
    When I authenticate as "user-0"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {
      "name": "Place (user-0)"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/places/1"

    When I authenticate as "user-1"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {
      "name": "Place (user-1)"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/places/2"

  Scenario: Filter by user
    When I send a "GET" request to "/api/places?user=user-0"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/places/1"

    When I send a "GET" request to "/api/places?user=user-1"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/places/2"

    When I send a "GET" request to "/api/places?user=user-2"
    Then the JSON node "hydra:member" should have 0 elements

  Scenario: Filter by group
    When I send a "GET" request to "/api/places?group=group-0"
    Then the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/places/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/places/2"

    When I send a "GET" request to "/api/places?group=group-1"
    Then the JSON node "hydra:member" should have 0 elements

  Scenario: Create Places
    When I authenticate as "user-2"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {
      "name": "Place (user-2)"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/places/3"

  Scenario: Filter by group
    When I send a "GET" request to "/api/places?group=group-0"
    Then the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/places/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/places/2"

    When I send a "GET" request to "/api/places?group=group-1"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/places/3"

  Scenario: Filter by editable_by
    When I send a "GET" request to "/api/places?editable_by=user-3"
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/places/3"

    When I send a "GET" request to "/api/places?editable_by=user-4"
    Then the JSON node "hydra:member" should have 0 elements

  @dropSchema
  Scenario: Drop schema
