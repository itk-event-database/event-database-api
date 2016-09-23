Feature: Occurrences
  In order to manage occurrences
  As a client software developer
  I need to be able to retrieve, create, update and delete occurrences trough the API.

  @createSchema
  Scenario: Anonymous access
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"

  Scenario: Count Occurrences
    When I sign in with username "api-read" and password "apipass"
    And I send a "GET" request to "/api/occurrences"
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

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    And I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Order by startDate
    When I authenticate as "api-read"
    And I send a "GET" request to "/api/occurrences?order[startDate]=asc"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/occurrences/2"

    When I send a "GET" request to "/api/occurrences?order[startDate]=desc"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/2"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/occurrences/1"

  Scenario: Filter by date range
    When I authenticate as "api-write"
    And I send a "GET" request to "/api/occurrences?startDate[after]=2000-01-01&endDate[before]=2010-01-10"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[0].event.occurrences" should have 2 elements

    When I send a "GET" request to "/api/occurrences?startDate[after]=2020-01-01&endDate[before]=2100-01-01"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/2"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[0].event.occurrences" should have 2 elements

  Scenario: Filter by place name
    When I authenticate as "api-write"
    And I send a "GET" request to "/api/occurrences?place.name=Some place"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/1"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[0].event.occurrences" should have 2 elements

    When I send a "GET" request to "/api/occurrences?place.name=Another place"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member[0].@id" should be equal to "/api/occurrences/2"
    And the JSON node "hydra:member[0].event.@id" should be equal to "/api/events/1"
    And the JSON node "hydra:member[0].event.occurrences" should have 2 elements

  Scenario: Delete event
    When I authenticate as "api-write"
    And I send a "DELETE" request to "/api/events/1"
    Then the response status code should be 204

  Scenario: Count Occurrences
    When I authenticate as "api-write"
    And I send a "GET" request to "/api/occurrences"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  @dropSchema
  Scenario: Drop schema
