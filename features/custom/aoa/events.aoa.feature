Feature: AoA Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following tags exist:
      | name     |
      | byliv    |
      | shopping |

  @createSchema
  Scenario: Count events
    When I add "Accept" header equal to "application/aoa+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 0 elements

  Scenario: Create an event
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Litteratur, musik og videokunst Off the Record",
      "excerpt": "Der afholdes, 10. november, Litterær Lounge på Teatret Svalegangens Off the Record scene.",
      "tags": ["byliv"],
      "image": "http://b.bimg.dk/node-images/279/15/x2048-u/15279463-01arh01arhlitter-rlounge-10jpg.jpeg",
      "description": "<p>Teatret Svalegangens Off The Record scene byder 10. november indenfor til en unik cocktail af ord, billeder og musik, når Litterær Lounge besøger teatret.</p>",
      "occurrences": [ {
        "startDate": "2016-11-10T21:00:00+00:00",
        "endDate": "2016-11-10T23:00:00+00:00",
        "place": {
          "name": "Teatret Svalegangen",
          "streetAddress": "Rosenkrantzgade",
          "postalCode": "8000",
          "addressLocality": "Århus C",
          "telephone": " 86138866",
          "url": "http://www.svalegangen.dk",
          "latitude": "56.1520354",
          "longitude": "10.2061561"
        }
      } ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node "@id" should be equal to "/api/events/1"

  Scenario: Get events (Accept: application/aoa+json)
    When I add "Accept" header equal to "application/aoa+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 1 element

  Scenario: Get events (events.jsonaoa)
    WhenI send a "GET" request to "/api/events.jsonaoa"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 1 element

  Scenario: Get events
    When I add "Accept" header equal to "application/aoa+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And print last JSON response
    And the JSON should not differ from:
    """
    [
      {
        "event_id": 1,
        "category": "byliv",
        "category_id": 1,
        "start_time": "Thu, 10 Nov 2016 21:00:00 +0100",
        "end_time": "Thu, 10 Nov 2016 23:00:00 +0100",
        "title": "Litteratur, musik og videokunst Off the Record",
        "supertitle": null,
        "summary": "Der afholdes, 10. november, Litterær Lounge på Teatret Svalegangens Off the Record scene.",
        "body_text": "<p>Teatret Svalegangens Off The Record scene byder 10. november indenfor til en unik cocktail af ord, billeder og musik, når Litterær Lounge besøger teatret.</p>",
        "images": {
          "image": "http://b.bimg.dk/node-images/279/15/x2048-u/15279463-01arh01arhlitter-rlounge-10jpg.jpeg",
          "image_full": "http://b.bimg.dk/node-images/279/15/x2048-u/15279463-01arh01arhlitter-rlounge-10jpg.jpeg",
          "caption": null
        },
        "location": {
          "id": 1,
          "name": "Teatret Svalegangen",
          "street": "Rosenkrantzgade",
          "postal_code": "8000",
          "city": "Århus C",
          "phone": " 86138866",
          "web_address": "http://www.svalegangen.dk",
          "lat": 56.1520354,
          "lng": 10.2061561,
          "details": {
            "1": {
              "date": "Torsdag, 2016-11-10",
              "time_start": "21:00",
              "time_end": "23:00"
            }
          }
        }
      }
    ]
    """

  Scenario: Create an event with multiple occurrences
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Rabat på smykker",
      "excerpt": "Spar op til 80 % på tidligere kollektioner, når JewlsCph holder smykkelagersalg 10.-12. november.",
      "tags": ["shopping"],
      "image": "http://a.bimg.dk/node-images/279/15/x2048-u/15279408-diamond.jpg",
      "description": "<p>10. til 12. november kan du komme til lagersalg. Det er JewlsCph, der sælger ud af de fine varer.</p>",
      "occurrences": [
        {
          "startDate": "2016-11-10T11:00:00+00:00",
          "endDate": "2016-11-10T17:30:00+00:00",
          "place": {
            "name": "JewlsCph",
            "streetAddress": "Klostergade",
            "postalCode": "8000",
            "addressLocality": "Århus C",
            "telephone": "",
            "url": "http://www.JEWLSCPH.com",
            "latitude": "56.158906",
            "longitude": "10.2090645"
          }
        },
        {
          "startDate": "2016-11-11T11:00:00+00:00",
          "endDate": "2016-11-11T18:00:00+00:00",
          "place": {
            "name": "JewlsCph",
            "streetAddress": "Klostergade",
            "postalCode": "8000",
            "addressLocality": "Århus C",
            "telephone": "",
            "url": "http://www.JEWLSCPH.com",
            "latitude": "56.158906",
            "longitude": "10.2090645"
          }
        },
        {
          "startDate": "2016-11-12T10:00:00+00:00",
          "endDate": "2016-11-12T15:00:00+00:00",
          "place": {
            "name": "JewlsCph",
            "streetAddress": "Klostergade",
            "postalCode": "8000",
            "addressLocality": "Århus C",
            "telephone": "",
            "url": "http://www.JEWLSCPH.com",
            "latitude": "56.158906",
            "longitude": "10.2090645"
          }
        }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node "@id" should be equal to "/api/events/2"

  Scenario: Get events (Accept: application/aoa+json)
    When I add "Accept" header equal to "application/aoa+json"
    And I send a "GET" request to "/api/events"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 2 element

  Scenario: Get events (events.jsonaoa)
    WhenI send a "GET" request to "/api/events.jsonaoa"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 2 element

  Scenario: Get event
    When I add "Accept" header equal to "application/aoa+json"
    And I send a "GET" request to "/api/events/2"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And print last JSON response
    And the JSON should not differ from:
    """
    {
      "event_id": 2,
      "category": "shopping",
      "category_id": 2,
      "start_time": "Thu, 10 Nov 2016 11:00:00 +0100",
      "end_time": "Sat, 12 Nov 2016 15:00:00 +0100",
      "title": "Rabat på smykker",
      "supertitle": null,
      "summary": "Spar op til 80 % på tidligere kollektioner, når JewlsCph holder smykkelagersalg 10.-12. november.",
      "body_text": "<p>10. til 12. november kan du komme til lagersalg. Det er JewlsCph, der sælger ud af de fine varer.</p>",
      "images": {
        "image": "http://a.bimg.dk/node-images/279/15/x2048-u/15279408-diamond.jpg",
        "image_full": "http://a.bimg.dk/node-images/279/15/x2048-u/15279408-diamond.jpg",
        "caption": null
      },
      "location": {
        "id": 2,
        "name": "JewlsCph",
        "street": "Klostergade",
        "postal_code": "8000",
        "city": "Århus C",
        "phone": "",
        "web_address": "http://www.JEWLSCPH.com",
        "lat": 56.158906,
        "lng": 10.2090645,
        "details": {
          "2": {
            "date": "Torsdag, 2016-11-10",
            "time_start": "11:00",
            "time_end": "17:30"
          },
          "3": {
            "date": "Fredag, 2016-11-11",
            "time_start": "11:00",
            "time_end": "18:00"
          },
          "4": {
            "date": "Lørdag, 2016-11-12",
            "time_start": "10:00",
            "time_end": "15:00"
          }
        }
      }
    }
    """

  @dropSchema
  Scenario: Drop schema
