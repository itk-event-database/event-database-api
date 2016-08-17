Feature: Events
  Events created by a user should be editable by other users in the user's groups.

  @createSchema
  Scenario:
    Given the following users exist:
      | username             | roles          | groups  |
      | user-0-group-0-write | ROLE_API_WRITE | group-0 |
      | user-1-group-0-write | ROLE_API_WRITE | group-0 |
      | user-0-group-1-write | ROLE_API_WRITE | group-1 |
      | user-1-group-1-write | ROLE_API_WRITE | group-1 |

    When I authenticate as "user-0-group-0-write"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Created by user-0-group-0-write"}
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/1"

    When I authenticate as "user-1-group-0-write"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Created by user-1-group-0-write"}
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Update an event
    When I authenticate as "user-1-group-0-write"
    And I send a "PUT" request to "/api/events/1" with body:
    """
    {"name": "Updated by user-1-group-0-write"}
    """
    Then the response status code should be 200

    When I authenticate as "user-0-group-0-write"
    When I send a "PUT" request to "/api/events/2" with body:
    """
    {"name": "Updated by user-0-group-0-write"}
    """
    Then the response status code should be 200

  Scenario: Delete an event
    When I authenticate as "user-0-group-1-write"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 403

    When I authenticate as "user-0-group-1-write"
    And I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 403

    When I authenticate as "user-1-group-0-write"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 204

    When I authenticate as "user-0-group-0-write"
    And I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 204

  @dropSchema
  Scenario:
    When I authenticate as "user-0-group-0-write"
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:totalItems" should be equal to the number 0
    And the JSON node "hydra:member" should have 0 elements
