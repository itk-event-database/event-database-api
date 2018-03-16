Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

    Given the following tags exist:
      | name |
      | a    |
      | b    |
      | c    |

  @createSchema
  Scenario: Create Events
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The first event",
      "occurrences": [ {
        "startDate": "+7 days",
        "endDate": "+8 days"
      }, {
        "startDate": "+14 days",
        "endDate": "+15 days"
      } ],
      "tags": ["a"]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/1"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The second event",
      "occurrences": [ {
        "startDate": "+7 days",
        "endDate": "+8 days"
      } ],
      "tags": ["b"]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/2"

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "The third event",
      "occurrences": [ {
        "startDate": "+7 days",
        "endDate": "+8 days"
      } ],
      "tags": ["a", "c"]
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to "/api/events/3"

  Scenario: Get occurrences
    When I send a "GET" request to "/api/occurrences"
    And the JSON node "hydra:member" should have 4 elements

  Scenario: Get occurrences by single tag
    When I send a "GET" request to "/api/occurrences?event.tags=a"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/occurrences/1"
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/occurrences/4"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/occurrences/2"

    When I send a "GET" request to "/api/occurrences?event.tags=b"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/3"

    When I send a "GET" request to "/api/occurrences?event.tags=c"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/4"

  Scenario: Get occurrences by tag list
    When I send a "GET" request to "/api/occurrences?event.tags=a,c"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/4"

    When I send a "GET" request to "/api/occurrences?event.tags=a,b"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Get occurrences by multiple tags
    When I send a "GET" request to "/api/occurrences?event.tags[]=a&event.tags[]=c"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/occurrences/4"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/occurrences/2"

    When I send a "GET" request to "/api/occurrences?event.tags[]=a&event.tags[]=b"
    And the JSON node "hydra:member" should have 4 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/occurrences/3"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/occurrences/4"
    And the JSON node "hydra:member[3].@id" should be equal to "/api/occurrences/2"

  @dropSchema
  Scenario: Drop schema
