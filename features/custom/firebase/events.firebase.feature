Feature: Firebase feed
  In order to sync events
  As a client software developer
  I need to be able to retrieve events through the firebase feed.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

    Given the following tags exist:
      | name     |
      | byliv    |
      | shopping |

  @createSchema
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
    And the JSON node "@id" should be equal to "/api/events/1"

  Scenario: Get firebase events feed
    And I send a "GET" request to "/api/firebase/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/firebase+json; charset=utf-8"

  Scenario: Get firebase organizers feed
    And I send a "GET" request to "/api/firebase/organizers"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/firebase+json; charset=utf-8"

  Scenario: Get firebase organizers feed
    And I send a "GET" request to "/api/firebase/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/firebase+json; charset=utf-8"

  @dropSchema
  Scenario: Drop schema
