Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Create an event
    When I authenticate as "api-write"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Created by api-write"}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/1",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "tags": [],
      "description": null,
      "image": null,
      "name": "Created by api-write",
      "url": null
    }
    """

    When I authenticate as "api-write2"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Created by api-write"}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/2",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "tags": [],
      "description": null,
      "image": null,
      "name": "Created by api-write",
      "url": null
    }
    """

  Scenario: Update an event
    When I authenticate as "api-write"
    And I send a "PUT" request to "/api/events/1" with body:
    """
    {"name": "Updated by api-write"}
    """
    Then the response status code should be 200

    When I send a "PUT" request to "/api/events/2" with body:
    """
    {"name": "Updated by api-write"}
    """
    Then the response status code should be 403

  Scenario: Delete an event
    When I authenticate as "api-write"
    And I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 403

    When I authenticate as "api-write2"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 403

    When I authenticate as "api-write2"
    And I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 204

    When I authenticate as "api-write"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 204

  @dropSchema
  Scenario: Drop schema
    When I authenticate as "api-read"
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:totalItems" should be equal to the number 0
    And the JSON node "hydra:member" should have 0 elements
