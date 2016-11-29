Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Create an event with multiple occurrences and a single place
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/events" with body:
     """
     {
       "name": "Repeating event with multiple places",
       "occurrences": [ {
         "startDate": "2001-01-01T00:00:00+00:00",
         "endDate": "2100-01-01T00:00:00+00:00",
         "place": {
           "name": "Place 1"
         }
       },
       {
         "startDate": "2002-01-01T00:00:00+00:00",
         "endDate": "2100-01-01T00:00:00+00:00",
         "place": {
           "name": "Place 1"
         }
       } ]
     }
     """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.event.response.schema.json"
    And the JSON node "name" should be equal to "Repeating event with multiple places"
    And the JSON node "occurrences" should have 2 elements
    And the JSON node "occurrences[0].place.@id" should be equal to "/api/places/1"
    And the JSON node "occurrences[1].place.@id" should be equal to "/api/places/1"
    And the JSON node "occurrences[1].@id" should be equal to "/api/occurrences/2"

  Scenario: Create an event with a single occurrence and a single place by reference
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/events" with body:
     """
     {
       "name": "Repeating event with multiple places",
       "occurrences": [ {
         "startDate": "2003-01-01T00:00:00+00:00",
         "endDate": "2100-01-01T00:00:00+00:00",
         "place": {
           "@id": "/api/places/1"
         }
       } ]
     }
     """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.event.response.schema.json"
    And the JSON node "name" should be equal to "Repeating event with multiple places"
    And the JSON node "occurrences" should have 1 element
    And the JSON node "occurrences[0].startDate" should be equal to "2003-01-01T00:00:00+00:00"
    And the JSON node "occurrences[0].endDate" should be equal to "2100-01-01T00:00:00+00:00"
    And the JSON node "occurrences[0].place.@id" should be equal to "/api/places/1"
    And the JSON node "occurrences[0].place.name" should be equal to "Place 1"
    And the JSON node "occurrences[0].@id" should be equal to "/api/occurrences/3"

  Scenario: Validate created event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/events/2"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "name" should be equal to "Repeating event with multiple places"

  Scenario: Update an event with a single occurrence and a single occurence by reference
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/api/events/2" with body:
     """
     {
       "name": "Updated repeating event with multiple places",
           "occurrences": [
               {
                   "@id": "\/api\/occurrences\/3",
                   "@type": "Occurrence",
                   "event": "\/api\/events\/2",
                   "startDate": "2003-01-01T00:00:00+01:00",
                   "endDate": "2100-01-01T00:00:00+01:00",
                   "place": {
                       "@id": "\/api\/places\/1",
                       "@type": "http:\/\/schema.org\/Place",
                       "telephone": null,
                       "email": null,
                       "logo": null,
                       "disabilityAccess": null,
                       "tags": [],
                       "description": null,
                       "image": null,
                       "name": "Place 1",
                       "url": null,
                       "videoUrl": null,
                       "langcode": null
                   },
                   "ticketPriceRange": null,
                   "eventStatusText": null
               }
           ]
     }
     """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.event.response.schema.json"
    And the JSON node "name" should be equal to "Updated repeating event with multiple places"
    And the JSON node "occurrences" should have 1 element
    And the JSON node "occurrences[0].@id" should be equal to "/api/occurrences/3"
    And the JSON node "occurrences[0].startDate" should be equal to "2003-01-01T00:00:00+01:00"
    And the JSON node "occurrences[0].endDate" should be equal to "2100-01-01T00:00:00+01:00"
    And the JSON node "occurrences[0].place.@id" should be equal to "/api/places/1"
    And the JSON node "occurrences[0].place.name" should be equal to "Place 1"

  Scenario: Validate updated event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/api/events/2"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "name" should be equal to "Updated repeating event with multiple places"
    And the JSON node "occurrences[0].@id" should be equal to "/api/occurrences/3"

  Scenario: Cannot create an event with a single occurrence and a single place by invalid reference
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/api/events" with body:
     """
     {
       "name": "Repeating event with multiple places",
       "occurrences": [ {
         "startDate": "2000-01T00:00:00+00:00",
         "endDate": "2100-01T00:00:00+00:00",
         "place": {
           "@id": "/api/places/2"
         }
       } ]
     }
     """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "hydra:description" should be equal to 'Item not found for "/api/places/2".'

  @dropSchema
  Scenario: Drop schema
