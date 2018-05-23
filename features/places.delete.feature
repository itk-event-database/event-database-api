Feature: Places
  In order to manage places
  As a client software developer
  I need to be able to retrieve, create, update and delete places trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

  @createSchema
  Scenario: Get deleted places
    When I send a "GET" request to "/api/places/deleted.json"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "" should have 0 elements

  Scenario: Create an place
    And I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {
      "name": "Dokk1",
      "streetAddress": "Hack Kampmanns Plads 2",
      "addressLocality": "Aarhus C",
      "postalCode": "8000",
      "email": "test@aarhus.dk",
      "addressCountry": "Denmark",
      "latitude": 56.164432,
      "longitude": 10.223941,
      "disabilityAccess": true
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/places/1"

    And I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/places" with body:
    """
    {
      "name": "Dokk2",
      "streetAddress": "Hack Kampmanns Plads 2",
      "addressLocality": "Aarhus C",
      "postalCode": "8000",
      "email": "test@aarhus.dk",
      "addressCountry": "Denmark",
      "latitude": 56.164432,
      "longitude": 10.223941,
      "disabilityAccess": true
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "@id" should be equal to "/api/places/2"

  Scenario: Delete an place
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/places/1"
    Then the response status code should be 204
    And the response should be empty

  Scenario: Get deleted places
    When I send a "GET" request to "/api/places/deleted.json"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "" should have 1 element
    And the JSON node "[0].id" should be equal to "1"

  Scenario: Delete an place
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" request to "/api/places/2"
    Then the response status code should be 204
    And the response should be empty

  Scenario: Get deleted places
    When I send a "GET" request to "/api/places/deleted.json"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "" should have 2 elements
    And the JSON node "[0].id" should be equal to "1"
    And the JSON node "[1].id" should be equal to "2"

  @dropSchema
  Scenario: Drop schema
