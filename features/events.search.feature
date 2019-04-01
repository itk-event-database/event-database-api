Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Create events
    When I authenticate as "api-write"
    And I add "content-type" header equal to "application/ld+json"
    And I add "accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The robots are coming!",
      "excerpt": "Should we be afraid?",
      "description": "Should we, the humans, be afraid? Or very afraid? What happens when the machines take over?",
      "image": "http://event-database-api.vm/files/mock/image.jpeg",
      "langcode": "da",
      "occurrences": [ {
        "startDate": "+24 hours",
        "endDate": "+25 hours"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/events/1"

    When I authenticate as "api-write"
    And I add "content-type" header equal to "application/ld+json"
    And I add "accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Are humans doomed?",
      "excerpt": "Will robots replace humans?",
      "description": "It's hard to predict the future, but what will happen if/when robots (or machines in general) take over the world?",
      "image": "http://event-database-api.vm/files/mock/image.jpeg",
      "langcode": "da",
      "occurrences": [ {
        "startDate": "+24 hours",
        "endDate": "+25 hours"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Count Events
    When I add "content-type" header equal to "application/ld+json"
    And I add "accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Search events
    When I add "accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?search[fields]=name&search[terms]=robot"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/events/1"

    When I add "accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?search[fields]=name&search[terms]=bum"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

    When I add "accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?search[fields]=name,excerpt&search[terms]=robot"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/events/1"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/events/2"

    When I add "accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?search[fields]=excerpt,description&search[terms]=robot"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/events/2"

    When I add "accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?search[fields]=exerpt,description&search[terms]=human"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/events/1"

    When I add "accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?search[fields]=description&search[terms]=human"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "content-type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/events/1"

  @dropSchema
  Scenario: Drop schema
