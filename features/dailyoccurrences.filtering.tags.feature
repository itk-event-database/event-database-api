Feature: Filter Daily Occurrences by tags
  In order to read daily occurrences
  As a client software developer
  I need to be able to retrieve occurrences trough the API filtered by tags.

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

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
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

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
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
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences"
    And the JSON node "hydra:member" should have 8 elements

  Scenario: Get occurrences by single tag
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.tags=a"
    And the JSON node "hydra:member" should have 6 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/1"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/2"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/3"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/4"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/7"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/8"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.tags=b"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/daily_occurrences/5"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/daily_occurrences/6"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.tags=c"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/daily_occurrences/7"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/daily_occurrences/8"

  Scenario: Get daily occurrences by tag list
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.tags=a,c"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/daily_occurrences/7"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/daily_occurrences/8"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.tags=a,b"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Get daily occurrences by multiple tags
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.tags[]=a&event.tags[]=c"
    And the JSON node "hydra:member" should have 6 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/1"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/2"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/3"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/4"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/7"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/8"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.tags[]=a&event.tags[]=b"
    And the JSON node "hydra:member" should have 8 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/1"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/2"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/3"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/4"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/5"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/6"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/7"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/8"

  Scenario: Get daily occurrences by event tags and name
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/daily_occurrences?event.name=third&event.tags[]=a&event.tags[]=c"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/7"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/daily_occurrences/8"

  @dropSchema
  Scenario: Drop schema
