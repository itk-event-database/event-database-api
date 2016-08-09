Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Events with tags
    When I authenticate as "api-write"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A tagged event",
      "tags": [ "a", "b" ]
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/1",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "tags": [ "a", "b" ],
      "description": null,
      "image": null,
      "name": "A tagged event",
      "url": null
    }
    """

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another tagged event",
      "tags": [ "b", "c" ]
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/2",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "tags": [ "b", "c" ],
      "description": null,
      "image": null,
      "name": "Another tagged event",
      "url": null
    }
    """

  Scenario: Filter by tags
    When I authenticate as "api-read"
    When I send a "GET" request to "/api/events?tags=a"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I send a "GET" request to "/api/events?tags=b"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

    When I send a "GET" request to "/api/events?tags=c"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  @dropSchema
  Scenario: Drop schema
