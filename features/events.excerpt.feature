Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-read   | apipass  | ROLE_API_READ  |
      | api-write  | apipass  | ROLE_API_WRITE |
      | api-write2 | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Events with html in description
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "An event",
      "description": "This is a strong <strong>word</strong>.",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "excerpt" should be equal to "This is a strong word."

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another event",
      "description": "This is a half strong <strong>word",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "excerpt" should be equal to "This is a half strong word"

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A script event",
      "description": "Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini abcdefghijklmn",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "excerpt" should be equal to "Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini abcde"

    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A script event",
      "description": "Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini tuo da gloriam.",
      "occurrences": [ { "startDate": "2000-01-01", "endDate": "2001-01-01" } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "excerpt" should be equal to "Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini tuo da gloriam. Non nobis, Domine, non nobis, sed nomini tuo d"

  @dropSchema
  Scenario: Drop schema
