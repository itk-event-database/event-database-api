Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following tags exist:
      | name   |
      | apple  |
      | banana |
      | citrus |

    And the following tags are unknown:
      | name   | tag    |
      | æble   | apple  |
      | banan  | banana |
      | citron | citrus |

  @createSchema
  Scenario: Check tags exist
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/tags"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/tags/1"
    And the JSON node "hydra:member[0].name" should be equal to "apple"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/tags/2"
    And the JSON node "hydra:member[1].name" should be equal to "banana"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/tags/3"
    And the JSON node "hydra:member[2].name" should be equal to "citrus"

  Scenario: Events with tags
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A tagged event",
      "tags": [ "apple", "Banana" ]
    }
    """
    Then the response status code should be 201
    And the JSON node "name" should be equal to "A tagged event"
    And the JSON node "tags" should have 2 elements
    And the JSON node "tags[0]" should be equal to "apple"
    And the JSON node "tags[1]" should be equal to "banana"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another tagged event",
      "tags": [ "banana", "CITRUS" ]
    }
    """
    Then the response status code should be 201
    And the JSON node "name" should be equal to "Another tagged event"
    And the JSON node "tags" should have 2 elements
    And the JSON node "tags[0]" should be equal to "banana"
    And the JSON node "tags[1]" should be equal to "citrus"

  Scenario: Events with unknown tags
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A tagged event",
      "tags": [ "æble" ]
    }
    """
    Then the response status code should be 201
    And the JSON node "tags" should have 1 element
    And the JSON node "tags[0]" should be equal to "apple"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A tagged event",
      "tags": [ "æble", "banan" ]
    }
    """
    Then the response status code should be 201
    And the JSON node "tags" should have 2 elements
    And the JSON node "tags[0]" should be equal to "apple"
    And the JSON node "tags[1]" should be equal to "banana"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A tagged event",
      "tags": [ "æble", "apple" ]
    }
    """
    Then the response status code should be 201
    And the JSON node "tags" should have 1 element
    And the JSON node "tags[0]" should be equal to "apple"

  Scenario: Read tags
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events/1"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/events/1"
    And the JSON node "tags" should have 2 elements
    And the JSON node "tags[0]" should be equal to "apple"
    And the JSON node "tags[1]" should be equal to "banana"

  Scenario: Filter by tags
    When I authenticate as "api-read"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?tags=apple"
    Then the JSON node "hydra:member" should have 4 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I send a "GET" request to "/api/events?tags=banana"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

    When I send a "GET" request to "/api/events?tags=citrus"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  @dropSchema
  Scenario: Drop schema
