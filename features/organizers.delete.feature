Feature: Organizers
  In order to manage organizers
  As a client software developer
  I need to be able to retrieve, create, update and delete organizers trough the API.

  Background:
    Given the following users exist:
      | username   | password | roles          |
      | api-write  | apipass  | ROLE_API_WRITE |

    And I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"

  @createSchema
  Scenario: Get deleted organizers
    When I send a "GET" request to "/api/organizers/deleted.json"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "" should have 0 elements

  @dropSchema
  Scenario: Drop schema
