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
      "name": "Big bang",
      "excerpt": "Stort bang! Større bum",
      "image": "http://event-database-api.vm/files/mock/image.jpeg",
      "ticketPurchaseUrl": "http://www.billetlugen.dk/referer/?r=266abe1b7fab4562a5c2531d0ae62171&p=/koeb/billetter/41677/",
      "videoUrl": "https://vimeo.com/183524061",
      "langcode": "da",
      "occurrences": [ {
        "startDate": "2000-01-01T00:00:00+00:00",
        "endDate": "2001-01-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "name" should be equal to "Big bang"
    # TODO: Fix handling of image downloads in tests.
    # And the JSON node "image" should be equal to "http://event-database-api.vm/files/mock/image.jpeg"
    And the JSON node "ticketPurchaseUrl" should be equal to "http://www.billetlugen.dk/referer/?r=266abe1b7fab4562a5c2531d0ae62171&p=/koeb/billetter/41677/"
    And the JSON node "videoUrl" should be equal to "https://vimeo.com/183524061"
    And the JSON node "langcode" should be equal to "da"

  Scenario: Update occurrences on an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/events/1" with body:
    """
    {
      "occurrences": [ {
        "startDate": "2100-01T00:00:00+00:00",
        "endDate": "2101-01T00:00:00+00:00"
      } ]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.event.response.schema.json"
    And the JSON node "name" should be equal to "Big bang"
    And the JSON node "occurrences" should have 1 element
    And the JSON node "occurrences[0].@id" should be equal to "/api/occurrences/2"
    And the JSON node "occurrences[0].startDate" should be equal to "2100-01-01T00:00:00+00:00"
    And the JSON node "occurrences[0].endDate" should be equal to "2101-01-01T00:00:00+00:00"

  Scenario: Count Events
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 element

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences?startDate[after]=@0"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 element

  @dropSchema
  Scenario: Drop schema
