@places
Feature: Places
  In order to manage places
  As a client software developer
  I need to be able to retrieve, create, update and delete places trough the API.

  @createSchema
  Scenario: No unauthorized access
    When I send a "GET" request to "/api/places"
    Then the response status code should be 401
    And the header "Content-Type" should be equal to "application/json"

  Scenario: Count Places
    When I sign in with username "api-read" and password "apipass"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Cannot create an place as read-only user
    When I authenticate as "api-read"
    And I send a "POST" request to "/api/places" with body:
    """
    {"name": "Dokk1"}
    """
    Then the response status code should be 403

  Scenario: Create a place
    When I authenticate as "api-write"
    And I send a "POST" request to "/api/places" with body:
    """
    {"name": "Dokk1"}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Place",
      "@id": "\/api\/places\/1",
      "@type": "http:\/\/schema.org\/Place",
      "logo": null,
      "occurrences": null,
      "description": null,
      "image": null,
      "name": "Dokk1",
      "url": null,
      "videoUrl": null,
      "langcode": null
    }
    """

  Scenario: Unauthorized attempt to delete a place
    When I authenticate as "api-read"
    And I send a "DELETE" request to "/api/places/2"
    Then the response status code should be 403

  Scenario: Count Places
    When I authenticate as "api-write"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 elements

  Scenario: Delete a place
    When I authenticate as "api-write"
    And I send a "DELETE" request to "/api/places/1"
    Then the response status code should be 204
    And the response should be empty

  @dropSchema
  Scenario: Drop schema
    When I authenticate as "api-read"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:totalItems" should be equal to the number 0
    And the JSON node "hydra:member" should have 0 elements
