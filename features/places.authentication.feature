Feature: Places Authentication
  In order to manage places
  As a client software developer
  I need to be able to retrieve, create, update and delete places trough the API.

  @createSchema
  Scenario: Read places anonymously
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:totalItems" should be equal to the number 0

  Scenario: Read places as read-only user
    When I authenticate as "api-read"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:totalItems" should be equal to the number 0

  Scenario: Create an place as read-only user
    When I authenticate as "api-read"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {"name": "Created by api-read"}
    """
    Then the response status code should be 403
    And the response should be in JSON

  Scenario: Create an place
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {"name": "Created by api-write"}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/places/1"
    And the JSON node "name" should be equal to "Created by api-write"

    When I authenticate as "api-write2"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {"name": "Created by api-write2"}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/places/2"
    And the JSON node "name" should be equal to "Created by api-write2"

  Scenario: Update an place
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/places/1" with body:
    """
    {"name": "Updated by api-write"}
    """
    Then the response status code should be 200

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/places/2" with body:
    """
    {"name": "Updated by api-write"}
    """
    Then the response status code should be 403

  Scenario: Delete an place
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/places/2"
    Then the response status code should be 403

    When I authenticate as "api-write2"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/places/1"
    Then the response status code should be 403

    When I authenticate as "api-write2"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/places/2"
    Then the response status code should be 204

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/places/1"
    Then the response status code should be 204

  @dropSchema
  Scenario: Drop schema
    When I authenticate as "api-read"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:totalItems" should be equal to the number 0
    And the JSON node "hydra:member" should have 0 elements
