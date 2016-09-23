Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Anonymous access
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"

  Scenario: Count Events
    When I sign in with username "api-read" and password "apipass"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Cannot create an event as read-only user
    When I authenticate as "api-read"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Big bang"}
    """
    Then the response status code should be 403

  Scenario: Create an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Big bang"}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should not differ from:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/1",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "tags": [],
      "description": null,
      "image": null,
      "name": "Big bang",
      "url": null,
      "langcode": null
    }
    """

  Scenario: Create an event with multiple occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Repeating event",
      "occurrences": [ {
        "startDate": "2000-01T00:00:00+00:00",
        "endDate": "2100-01T00:00:00+00:00"
      },
      {
        "startDate": "2000-01T00:00:00+00:00",
        "endDate": "2100-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.event.response.schema.json"
    And the JSON should not differ from:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/2",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [
        {
          "@id": "\/api\/occurrences\/1",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2000-01-01T00:00:00+00:00",
          "endDate": "2100-01-01T00:00:00+00:00",
          "place": null
        },
        {
          "@id": "\/api\/occurrences\/2",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2000-01-01T00:00:00+00:00",
          "endDate": "2100-01-01T00:00:00+00:00",
          "place": null
        }
      ],
      "tags": [],
      "description": null,
      "image": null,
      "name": "Repeating event",
      "url": null,
      "langcode": null
    }
    """

  Scenario: Count Events
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 2 elements
    # And the JSON should be valid according to the schema "features/schema/api.events.response.schema.json"

  Scenario: Update an event with multiple occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/events/2" with body:
    """
    {
      "name": "Repeating event (updated)",
      "occurrences": [ {
        "startDate": "2000-01T00:00:00+00:00",
        "endDate": "2100-01T00:00:00+00:00"
      },
      {
        "startDate": "2000-01T00:00:00+00:00",
        "endDate": "2100-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/2",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [
        {
          "@id": "\/api\/occurrences\/3",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2000-01-01T00:00:00+00:00",
          "endDate": "2100-01-01T00:00:00+00:00",
          "place": null
        },
        {
          "@id": "\/api\/occurrences\/4",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2000-01-01T00:00:00+00:00",
          "endDate": "2100-01-01T00:00:00+00:00",
          "place": null
        }
      ],
      "tags": [],
      "description": null,
      "image": null,
      "name": "Repeating event (updated)",
      "url": null,
      "langcode": null
    }
    """

  Scenario: Unauthorized attempt to delete an event
    When I authenticate as "api-read"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 403

  Scenario: Delete an event with multiple occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 204
    And the response should be empty

  Scenario: Count Events
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 elements

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Delete an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 204
    And the response should be empty

  @dropSchema
  Scenario: Drop schema
    When I authenticate as "api-read"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:totalItems" should be equal to the number 0
    And the JSON node "hydra:member" should have 0 elements
