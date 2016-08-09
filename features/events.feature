Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: No unauthorized access
    When I send a "GET" request to "/api/events"
    Then the response status code should be 401
    And the header "Content-Type" should be equal to "application/json"
    Then print the corresponding curl command

  Scenario: Count Events
    When I sign in with username "api-read" and password "apipass"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Cannot create an event as read-only user
    When I authenticate as "api-read"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Big bang"}
    """
    Then the response status code should be 403

  Scenario: Create an event
    When I authenticate as "api-write"
    And I send a "POST" request to "/api/events" with body:
    """
    {"name": "Big bang"}
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
      "name": "Big bang",
      "url": null
    }
    """

  Scenario: Create an event with multiple occurrences
    When I authenticate as "api-write"
    When I send a "POST" request to "/api/events" with body:
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
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON should be equal to:
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
          "venue": null
        },
        {
          "@id": "\/api\/occurrences\/2",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2000-01-01T00:00:00+00:00",
          "endDate": "2100-01-01T00:00:00+00:00",
          "venue": null
        }
      ],
      "tags": [],
      "description": null,
      "image": null,
      "name": "Repeating event",
      "url": null
    }
    """

  Scenario: Count Events
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Update an event with multiple occurrences
    When I authenticate as "api-write"
    When I send a "PUT" request to "/api/events/2" with body:
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
    And the header "Content-Type" should be equal to "application/ld+json"
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
          "venue": null
        },
        {
          "@id": "\/api\/occurrences\/4",
          "@type": "Occurrence",
          "event": "\/api\/events\/2",
          "startDate": "2000-01-01T00:00:00+00:00",
          "endDate": "2100-01-01T00:00:00+00:00",
          "venue": null
        }
      ],
      "tags": [],
      "description": null,
      "image": null,
      "name": "Repeating event (updated)",
      "url": null
    }
    """

  Scenario: Unauthorized attempt to delete an event
    When I authenticate as "api-read"
    When I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 403

  Scenario: Delete an event with multiple occurrences
    When I authenticate as "api-write"
    When I send a "DELETE" request to "/api/events/2"
    Then the response status code should be 204
    And the response should be empty

  Scenario: Count Events
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 1 elements

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    When I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Delete an event
    When I authenticate as "api-write"
    When I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 204
    And the response should be empty

  @dropSchema
  Scenario: Drop schema
    When I authenticate as "api-read"
    When I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json"
    And the JSON node "hydra:totalItems" should be equal to the number 0
    And the JSON node "hydra:member" should have 0 elements
