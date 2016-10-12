Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Create tags
    Given the following tags exist:
      | name   | slug   |
      | apple  | apple  |
      | banana | banana |
      | citrus | citrus |

    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/api/tags"
    Then the response status code should be 200
    And the JSON should not differ from:
    """
    [
      {
        "id": "\/api\/tags\/1",
        "name": "apple"
      },
      {
        "id": "\/api\/tags\/2",
        "name": "banana"
      },
      {
        "id": "\/api\/tags\/3",
        "name": "citrus"
      }
    ]
    """

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
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/1",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "ticketPurchaseUrl": null,
      "tags": [ "apple", "banana" ],
      "description": null,
      "image": null,
      "name": "A tagged event",
      "url": null,
      "videoUrl": null,
      "langcode": null
    }
    """

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another tagged event",
      "tags": [ "banana", "CITRUS" ]
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
      "ticketPurchaseUrl": null,
      "tags": [ "banana", "citrus" ],
      "description": null,
      "image": null,
      "name": "Another tagged event",
      "url": null,
      "videoUrl": null,
      "langcode": null
    }
    """

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
    Then the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I send a "GET" request to "/api/events?tags=banana"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/events/2"

    When I send a "GET" request to "/api/events?tags=citrus"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/2"

  @dropSchema
  Scenario: Drop schema
