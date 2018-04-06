Feature: Organizers
  In order to manage organizers
  As a client software developer
  I need to be able to retrieve, create, update and delete organizers trough the API.

  Background:
    Given the following AppBundle\Entity\Organizer entities exist:
      | id | name         | email                     | url                   |
      |  2 | Company B    | mail@companyb.example.com | companyb.example.com  |
      |  1 | Company A    | mail@companya.example.com | companya.example.com  |
      |  3 | Damage, Inc. | damageinc@example.com     | damageinc.example.com |

  @createSchema
  Scenario: Count Organizers
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 3 elements

  Scenario: Filter by name
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers?name=b"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 1 element
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/organizers/2"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers?name=company"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/organizers/1"
    And the JSON node "hydra:member" should contain 1 element with "@id" equal to "/api/organizers/2"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers?name=xxx"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 0 elements

  Scenario: Order by name
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/organizers/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/organizers/2"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/organizers/3"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers?order[name]=asc"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/organizers/1"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/organizers/2"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/organizers/3"

    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/api/organizers?order[name]=desc"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON node "hydra:member" should have 3 elements
    And the JSON node "hydra:member[0].@id" should be equal to "/api/organizers/3"
    And the JSON node "hydra:member[1].@id" should be equal to "/api/organizers/2"
    And the JSON node "hydra:member[2].@id" should be equal to "/api/organizers/1"

  @dropSchema
  Scenario: Drop schema
