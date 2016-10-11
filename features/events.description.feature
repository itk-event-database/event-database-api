Feature: Events
  In order to manage events
  As a client software developer
  I need to be able to retrieve, create, update and delete events trough the API.

  @createSchema
  Scenario: Events with html in description
    When I authenticate as "api-write"
    And I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "An event",
      "description": "This is a strong <strong>word</strong>."
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/1",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "ticketPurchaseUrl": null,
      "tags": [],
      "description": "This is a strong <strong>word</strong>.",
      "image": null,
      "name": "An event",
      "url": null,
      "videoUrl": null,
      "langcode": null
    }
    """

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "Another event",
      "description": "This is a half strong <strong>word"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/2",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "ticketPurchaseUrl": null,
      "tags": [],
      "description": "This is a half strong <strong>word</strong>",
      "image": null,
      "name": "Another event",
      "url": null,
      "videoUrl": null,
      "langcode": null
    }
    """

    When I send a "POST" request to "/api/events" with body:
    """
    {
      "name": "A script event",
      "description": "This is a <script src='http://hack.com/'></script>"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "@context": "\/api\/contexts\/Event",
      "@id": "\/api\/events\/3",
      "@type": "http:\/\/schema.org\/Event",
      "occurrences": [],
      "ticketPurchaseUrl": null,
      "tags": [],
      "description": "This is a ",
      "image": null,
      "name": "A script event",
      "url": null,
      "videoUrl": null,
      "langcode": null
    }
    """

  @dropSchema
  Scenario: Drop schema
