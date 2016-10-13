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
    {
      "name": "Big bang",
      "image": "http://static.billetlugen.dk/images/events/b/41677.jpg",
      "ticketPurchaseUrl": "http://www.billetlugen.dk/referer/?r=266abe1b7fab4562a5c2531d0ae62171&p=/koeb/billetter/41677/",
      "videoUrl": "https://vimeo.com/183524061",
      "langcode": "da"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "name" should be equal to "Big bang"
    And the JSON node "image" should be equal to "http://static.billetlugen.dk/images/events/b/41677.jpg"
    And the JSON node "ticketPurchaseUrl" should be equal to "http://www.billetlugen.dk/referer/?r=266abe1b7fab4562a5c2531d0ae62171&p=/koeb/billetter/41677/"
    And the JSON node "videoUrl" should be equal to "https://vimeo.com/183524061"
    And the JSON node "langcode" should be equal to "da"

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
    And the JSON node "name" should be equal to "Repeating event"
    And the JSON node "occurrences" should have 2 elements
    And the JSON node "occurrences[0].startDate" should be equal to "2000-01-01T00:00:00+00:00"
    And the JSON node "occurrences[0].endDate" should be equal to "2100-01-01T00:00:00+00:00"
    And the JSON node "occurrences[1].startDate" should be equal to "2000-01-01T00:00:00+00:00"
    And the JSON node "occurrences[1].endDate" should be equal to "2100-01-01T00:00:00+00:00"

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
    And the JSON node "name" should be equal to "Repeating event (updated)"
    And the JSON node "occurrences" should have 2 elements
    And the JSON node "occurrences[0].startDate" should be equal to "2000-01-01T00:00:00+00:00"
    And the JSON node "occurrences[0].endDate" should be equal to "2100-01-01T00:00:00+00:00"
    And the JSON node "occurrences[1].startDate" should be equal to "2000-01-01T00:00:00+00:00"
    And the JSON node "occurrences[1].endDate" should be equal to "2100-01-01T00:00:00+00:00"

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
