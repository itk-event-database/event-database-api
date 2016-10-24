@places
Feature: Places
  In order to manage places
  As a client software developer
  I need to be able to retrieve, create, update and delete places trough the API.

  @createSchema
  Scenario: Anonymous access
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"

  Scenario: Count Places
    When I sign in with username "api-read" and password "apipass"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Cannot create an place as read-only user
    When I authenticate as "api-read"
    And I send a "POST" request to "/api/places" with body:
    """
    {"name": "Dokk1"}
    """
    Then the response status code should be 403

  Scenario: Create a place
    When I authenticate as "api-write"
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
    And the JSON should be valid according to the schema "features/schema/api.place.response.schema.json"
    And the JSON node "@id" should be equal to "/api/places/1"
    And the JSON node "name" should be equal to "Dokk1"
    And the JSON node "streetAddress" should be equal to "Hack Kampmanns Plads 2"
    And the JSON node "addressLocality" should be equal to "Aarhus C"
    And the JSON node "postalCode" should be equal to "8000"
    And the JSON node "addressCountry" should be equal to "Denmark"
    And the JSON node "latitude" should be equal to 56.164432
    And the JSON node "longitude" should be equal to 10.223941
    And the JSON node "email" should be equal to "test@aarhus.dk"
    And the JSON node disabilityAccess" should be true

  Scenario: Get a place
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "Get" request to "/api/places/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be valid according to the schema "features/schema/api.place.response.schema.json"
    And the JSON node "@id" should be equal to "/api/places/1"
    And the JSON node "name" should be equal to "Dokk1"
    And the JSON node "streetAddress" should be equal to "Hack Kampmanns Plads 2"
    And the JSON node "addressLocality" should be equal to "Aarhus C"
    And the JSON node "postalCode" should be equal to "8000"
    And the JSON node "addressCountry" should be equal to "Denmark"
    And the JSON node "latitude" should be equal to 56.164432
    And the JSON node "longitude" should be equal to 10.223941
    And the JSON node disabilityAccess" should be true

  Scenario: Unauthorized attempt to delete a place
    When I authenticate as "api-read"
    And I send a "DELETE" request to "/api/places/1"
    Then the response status code should be 403

  Scenario: Count Places
    When I authenticate as "api-write"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 elements

  Scenario: Delete a place
    When I authenticate as "api-write"
    And I send a "DELETE" request to "/api/places/1"
    Then the response status code should be 204
    And the response should be empty

  @dropSchema
  Scenario: Drop schema
    When I authenticate as "api-read"
    And I send a "GET" request to "/api/places"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:totalItems" should be equal to the number 0
    And the JSON node "hydra:member" should have 0 elements
