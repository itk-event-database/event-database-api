Feature: Occurrences
  In order to manage occurrences
  As a client software developer
  I need to be able to retrieve, create, update and delete occurrences trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-read   | apipass  | ROLE_API_READ  |
      | api-write  | apipass  | ROLE_API_WRITE |
      | api-write2 | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Anonymous access
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"

  Scenario: No write endpoints for daily occurrences
    When I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/calendar"
    Then the response status code should be 405

    When I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/calendar"
    Then the response status code should be 405

    When I add "Accept" header equal to "application/ld+json"
    And I send a "PATCH" request to "/api/calendar"
    Then the response status code should be 405

    When I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/calendar"
    Then the response status code should be 405

  Scenario: Count Occurrences
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Create an event with multiple occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Repeating event",
      "occurrences": [ {
        "startDate": "2011-01-02T13:00:00+00:00",
        "endDate": "2011-01-02T14:00:00+00:00",
        "place": {
          "name": "Some place"
        }
      },
      {
        "startDate": "2012-01-02T13:00:00+00:00",
        "endDate": "2012-01-04T14:00:00+00:00",
        "place": {
          "name": "Another place"
        }
      } ]
    }
    """
    Then the response status code should be 201

  Scenario: Count DailyOccurrences
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 4 elements
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"

  Scenario: Order by startDate
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&order[startDate]=asc"
    And the JSON node "hydra:member" should have 4 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/calendar/2"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&order[startDate]=desc"
    And print last JSON response
    And the JSON node "hydra:member" should have 4 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/4"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/calendar/3"

  Scenario: Filter by date range
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=2000-01-01&endDate[before]=2011-01-10"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/1"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=2012-01-01&endDate[before]=2100-01-01"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/2"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/calendar/3"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/calendar/4"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"

  Scenario: Filter by place name
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&place.name=Some place"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/1"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&place.name=Another place&order[startDate]=asc"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/2"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/calendar/3"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/calendar/4"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"

  Scenario: Filter by event name
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&event.name=Repeating+event"
    And the JSON node "hydra:member" should have 4 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/calendar/2"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/calendar/3"
    And the JSON node "hydra:member[3].@id" should be equal to "/api/calendar/4"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&event.name=eat"
    And the JSON node "hydra:member" should have 4 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/calendar/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/calendar/2"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/calendar/3"
    And the JSON node "hydra:member[3].@id" should be equal to "/api/calendar/4"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&event.name=Another event"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Filter by created by
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&event.createdBy=2"
    And the JSON node "hydra:member" should have 4 elements

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&event.createdBy[]=2&event.createdBy[]=87"
    And the JSON node "hydra:member" should have 4 elements

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&event.createdBy=87"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Delete event
    When I authenticate as "api-write"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 204

  Scenario: Count Daily Occurrences
    When I authenticate as "api-write"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Create an event with multiple occurrences should create multiple daily occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Repeating event",
      "occurrences": [ {
        "startDate": "2000-01-01",
        "endDate": "2001-01-01",
        "place": {
          "name": "Some place"
        }
      },
      {
        "startDate": "2020-01-01",
        "endDate": "2100-01-01",
        "place": {
          "name": "Another place"
        }
      } ]
    }
    """
    Then the response status code should be 201
    And the JSON node "occurrences" should have 2 elements
    And the JSON node "occurrences[0].@id" should be equal to "/api/occurrences/3"
    And the JSON node "occurrences[1].@id" should be equal to "/api/occurrences/4"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0"
    And the JSON node "hydra:member" should have 30 elements
    And the JSON node "hydra:totalItems" should be equal to 29588
    And the JSON node "hydra:view.hydra:last" should be equal to "/api/calendar?startDate%5Bafter%5D=%400&endDate%5Bafter%5D=%400&page=987"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&page=987"
    And the JSON node "hydra:member" should have 8 elements

  Scenario: Update an event with multiple occurrences it should update the relevant daily occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/events/2" with body:
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
               "startDate": "2000-01-01T00:00:00+01:00",
               "endDate": "2000-01-31T00:00:00+01:00",
               "place": {
                   "@id": "\/api\/places\/1",
                   "@type": "http:\/\/schema.org\/Place",
                   "logo": null,
                   "description": null,
                   "image": null,
                   "name": "Some new place",
                   "url": null,
                   "videoUrl": null,
                   "langcode": null
               },
               "ticketPriceRange": "20-30 Kr.",
               "eventStatusText": null
           },
           {
               "@id": "\/api\/occurrences\/4",
               "@type": "Occurrence",
               "event": "\/api\/events\/2",
               "startDate": "2020-01-01T00:00:00+01:00",
               "endDate": "2030-01-01T00:00:00+01:00",
               "place": {
                   "@id": "\/api\/places\/2",
                   "@type": "http:\/\/schema.org\/Place",
                   "logo": null,
                   "description": null,
                   "image": null,
                   "name": "Another new place",
                   "url": null,
                   "videoUrl": null,
                   "langcode": null
               },
               "ticketPriceRange": "40-50 Kr.",
               "eventStatusText": null
           }
       ],
       "ticketPurchaseUrl": null,
       "excerpt": null,
       "tags": [],
       "description": null,
       "image": null,
       "name": "Repeating event",
       "url": null,
       "videoUrl": null,
       "langcode": null
     }
     """

    Then the response status code should be 200
    And the JSON node "occurrences" should have 2 elements
    And the JSON node "occurrences[0].@id" should be equal to "/api/occurrences/3"
    And the JSON node "occurrences[1].@id" should be equal to "/api/occurrences/4"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0"
    And the JSON node "hydra:member" should have 30 elements
    And the JSON node "hydra:totalItems" should be equal to 3685
    And the JSON node "hydra:view.hydra:last" should be equal to "/api/calendar?startDate%5Bafter%5D=%400&endDate%5Bafter%5D=%400&page=123"
    And the JSON node "hydra:member[0].place.name" should be equal to "Some new place"
    And the JSON node "hydra:member[0].ticketPriceRange" should be equal to "20-30 Kr."

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0&page=123"
    And the JSON node "hydra:member" should have 25 elements
    And the JSON node "hydra:member[0].place.name" should be equal to "Another new place"
    And the JSON node "hydra:member[0].ticketPriceRange" should be equal to "40-50 Kr."

  Scenario: Update an event with a single occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" request to "/api/events/2" with body:
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
               "startDate": "2000-01-01T00:00:00+01:00",
               "endDate": "2000-01-03T00:00:00+01:00",
               "place": {
                   "@id": "\/api\/places\/1",
                   "@type": "http:\/\/schema.org\/Place",
                   "logo": null,
                   "description": null,
                   "image": null,
                   "name": "Some place",
                   "url": null,
                   "videoUrl": null,
                   "langcode": null
               },
               "ticketPriceRange": null,
               "eventStatusText": null
           }
       ],
       "ticketPurchaseUrl": null,
       "excerpt": null,
       "tags": [],
       "description": null,
       "image": null,
       "name": "Repeating event",
       "url": null,
       "videoUrl": null,
       "langcode": null
     }
     """

    Then the response status code should be 200
    And the JSON node "occurrences" should have 1 element
    And the JSON node "occurrences[0].@id" should be equal to "/api/occurrences/3"

    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/calendar?startDate[after]=@0&endDate[after]=@0"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:totalItems" should be equal to 3

  @dropSchema
  Scenario: Drop schema
