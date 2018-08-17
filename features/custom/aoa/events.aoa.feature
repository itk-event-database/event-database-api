Feature: AoA Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-read   | apipass  | ROLE_API_READ  |
      | api-write  | apipass  | ROLE_API_WRITE |
      | api-write2 | apipass  | ROLE_API_WRITE |

    Given the following tags exist:
      | name     |
      | byliv    |
      | shopping |

  @createSchema
  Scenario: Count events
    When I add "Accept" header equal to "application/aoa+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0"
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
      "image": "http://event-database-api.vm/files/mock/image.jpeg",
      "description": "<p>Harris Lambrakis Quartet, et af Grækenlands mest respekterede jazzorkestre, blev dannet I 2006 med en klassisk klavertrio som fundament, men med et, i jazzen usædvanligt, blæseinstrument i front, nemlig en ney, en tyrkiak fløjte som er et af de ældste musikinstrumenter, der stadig er i brug.</p><p><strong>Læs også: </strong><a href=\"http://www.aoa.dk/musik/jazzsvaervaegtere-spiller-paa-atlas\" target=\"_blank\">Jazzsværvægtere spiller på Atlas</a></p><p>Gruppen henter sin inspiration fra flere forskellige genrer, både den modale jazz, græsk musik, klange fra det østlige middelhav og overordnet improvisationsmusikken.</p><p><strong>Læs også: </strong><a href=\"http://www.aoa.dk/musik/fem-aartier-med-the-savage-rose\" target=\"_blank\">Fem årtier med The Savage Rose</a></p><p>Oplev de græske musikere live, når Harris Lambrakis Quartet, 6. november, optræder på Kunsthal Aarhus.</p><p><strong>Harris Lambrakis Quartet Kunsthal Aarhus, J.M. Mørks Gade 13, Aarhus C., 6. november, kl. 17, entré.</strong></p>",
      "occurrences": [ {
        "startDate": "2016-11-10T21:00:00+00:00",
        "//endDate": "2016-11-10T23:00:00+00:00",
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
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 1 element

  Scenario: Get events (events.jsonaoa)
    WhenI send a "GET" request to "/api/events.jsonaoa?occurrences.startDate[after]=@0"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 1 element

  Scenario: Get events
    When I add "Accept" header equal to "application/aoa+json"
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the JSON should not differ from:
    """
    [
      {
        "event_id": 1,
        "category": "Byliv",
        "category_id": 64237,
        "start_time": "Thu, 10 Nov 2016 21:00:00 +0000",
        "end_time": "Thu, 10 Nov 2016 22:00:00 +0000",
        "title": "Litteratur, musik og videokunst Off the Record",
        "supertitle": "",
        "summary": "Der afholdes, 10. november, Litterær Lounge på Teatret Svalegangens Off the Record scene.",
        "body_text": "\u003Cp\u003EHarris Lambrakis Quartet, et af Grækenlands mest respekterede jazzorkestre, blev dannet I 2006 med en klassisk klavertrio som fundament, men med et, i jazzen usædvanligt, blæseinstrument i front, nemlig en ney, en tyrkiak fløjte som er et af de ældste musikinstrumenter, der stadig er i brug.\u003C\u002Fp\u003E\u003Cp\u003E\u003Cstrong\u003ELæs også: \u003C\u002Fstrong\u003E\u003Ca href=\u0022http:\u002F\u002Fwww.aoa.dk\u002Fmusik\u002Fjazzsvaervaegtere-spiller-paa-atlas\u0022\u003EJazzsværvægtere spiller på Atlas\u003C\u002Fa\u003E\u003C\u002Fp\u003E\u003Cp\u003EGruppen henter sin inspiration fra flere forskellige genrer, både den modale jazz, græsk musik, klange fra det østlige middelhav og overordnet improvisationsmusikken.\u003C\u002Fp\u003E\u003Cp\u003E\u003Cstrong\u003ELæs også: \u003C\u002Fstrong\u003E\u003Ca href=\u0022http:\u002F\u002Fwww.aoa.dk\u002Fmusik\u002Ffem-aartier-med-the-savage-rose\u0022\u003EFem årtier med The Savage Rose\u003C\u002Fa\u003E\u003C\u002Fp\u003E\u003Cp\u003EOplev de græske musikere live, når Harris Lambrakis Quartet, 6. november, optræder på Kunsthal Aarhus.\u003C\u002Fp\u003E\u003Cp\u003E\u003Cstrong\u003EHarris Lambrakis Quartet Kunsthal Aarhus, J.M. Mørks Gade 13, Aarhus C., 6. november, kl. 17, entré.\u003C\u002Fstrong\u003E\u003C\u002Fp\u003E",
        "images": {
          "image": "http://event-database-api.vm/files/mock/image.jpeg",
          "image_full": "http://event-database-api.vm/files/mock/image.jpeg",
          "caption": ""
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
              "time_end": "22:00"
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
      "image": "http://event-database-api.vm/files/mock/image.jpeg",
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
    And I send a "GET" request to "/api/events?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/aoa+json; charset=utf-8"
    And the response should be in JSON
    And the JSON node "" should have 2 element

  Scenario: Get events (events.jsonaoa)
    When I send a "GET" request to "/api/events.jsonaoa?occurrences.startDate[after]=@0&occurrences.endDate[after]=@0"
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
    And the JSON should not differ from:
    """
    {
      "event_id": 2,
      "category": "Byliv",
      "category_id": 64237,
      "start_time": "Thu, 10 Nov 2016 11:00:00 +0000",
      "end_time": "Sat, 12 Nov 2016 15:00:00 +0000",
      "title": "Rabat på smykker",
      "supertitle": "",
      "summary": "Spar op til 80 % på tidligere kollektioner, når JewlsCph holder smykkelagersalg 10.-12. november.",
      "body_text": "\u003Cp\u003E10. til 12. november kan du komme til lagersalg. Det er JewlsCph, der sælger ud af de fine varer.\u003C\u002Fp\u003E",
      "images": {
        "image": "http://event-database-api.vm/files/mock/image.jpeg",
        "image_full": "http://event-database-api.vm/files/mock/image.jpeg",
        "caption": ""
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
