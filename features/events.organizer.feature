Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Create an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The first event",
      "organizer": {
        "name": "The organizer",
        "email": "organizer@example.com",
        "url": "https://example.com/organizer"
      },
      "partnerOrganizers": [
        {
          "name": "The First Partner",
          "email": "first.partner@example.com",
          "url": "https://partner-1.com/organizer"
        },
        {
          "name": "The Second Partner",
          "email": "second.partner@example.com",
          "url": "https://partner-2.com/organizer"
        }
      ],
      "occurrences": [ {
        "startDate": "2000-01-01T00:00:00+00:00",
        "endDate": "2001-01-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "organizer.name" should be equal to "The organizer"
    And the JSON node "organizer.@id" should be equal to "/api/organizers/1"
    And print last JSON response
    And the JSON node "partnerOrganizers" should have 2 element
    And the JSON node "partnerOrganizers[0].name" should be equal to "The First Partner"
    And the JSON node "partnerOrganizers[0].@id" should be equal to "/api/organizers/2"
    And the JSON node "partnerOrganizers[1].name" should be equal to "The Second Partner"
    And the JSON node "partnerOrganizers[1].@id" should be equal to "/api/organizers/3"

  Scenario: Create another event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another event",
      "organizer": {
        "name": "The organizer",
        "email": "organizer@exampl.com",
        "url": "http://example.com/organizer"
      },
      "occurrences": [ {
        "startDate": "2000-01-01T00:00:00+00:00",
        "endDate": "2001-01-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "organizer.name" should be equal to "The organizer"
    And the JSON node "organizer.@id" should be equal to "/api/organizers/1"

  Scenario: Miss-spell organizer name
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another event",
      "organizer": {
        "name": "The orgnizer",
        "email": "organizer@example.com",
        "url": "http://example.com/organizer"
      },
      "occurrences": [ {
        "startDate": "2000-01-01T00:00:00+00:00",
        "endDate": "2001-01-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "organizer.name" should be equal to "The organizer"
    And the JSON node "organizer.@id" should be equal to "/api/organizers/1"

  Scenario: Miss-spell organizer email
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another event",
      "organizer": {
        "name": "The organizer",
        "email": "organizr@example.com",
        "url": "http://example.com/organizer"
      },
      "occurrences": [ {
        "startDate": "2000-01-01T00:00:00+00:00",
        "endDate": "2001-01-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "organizer.name" should be equal to "The organizer"
    And the JSON node "organizer.@id" should be equal to "/api/organizers/1"

  Scenario: Count organizers
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 element

  Scenario: Create an event with a new organizer
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The first event",
      "organizer": {
        "name": "Damage, Inc.",
        "email": "damage@metallica.com",
        "url": "http://metallica.com/damage"
      },
      "occurrences": [ {
        "startDate": "2000-01-01T00:00:00+00:00",
        "endDate": "2001-01-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "organizer.name" should be equal to "Damage, Inc."
    And the JSON node "organizer.@id" should be equal to "/api/organizers/2"

  Scenario: Count organizers
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Create an event with a known organizer
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The third event",
      "organizer": {
        "@id": "/api/organizers/2"
      },
      "occurrences": [ {
        "startDate": "2000-01-01T00:00:00+00:00",
        "endDate": "2001-01-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "organizer.name" should be equal to "Damage, Inc."
    And the JSON node "organizer.@id" should be equal to "/api/organizers/2"

  @dropSchema
  Scenario: Drop schema
