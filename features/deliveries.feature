Feature: Deliveries

  Scenario: Create delivery with pickup & dropoff
    Given the database is empty
    And the fixtures file "stores.yml" is loaded
    And the user "bob" is loaded:
      | email      | bob@coopcycle.org |
      | password   | 123456            |
    And the store with name "Acme" belongs to user "bob"
    And the store with name "Acme" has token "abc123456789"
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Authorization" header equal to "Bearer abc123456789"
    And I send a "POST" request to "/api/deliveries" with body:
      """
      {
        "pickup": {
          "address": "24, Rue de la Paix",
          "doneBefore": "tomorrow 13:00"
        },
        "dropoff": {
          "address": "48, Rue de Rivoli",
          "doneBefore": "tomorrow 13:30"
        }
      }
      """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should match:
    """
    {
      "@context":"/api/contexts/Delivery",
      "@id":"@string@.startsWith('/api/deliveries')",
      "@type":"http://schema.org/ParcelDelivery",
      "id":@integer@,
      "status":null,
      "pickup":{
        "address":{
          "@context":"/api/contexts/Address",
          "@id":"@string@.startsWith('/api/addresses')",
          "@type":"http://schema.org/Place",
          "geo":{
            "latitude":@double@,
            "longitude":@double@
          },
          "streetAddress":@string@,
          "telephone":null,
          "name":null
        }
      },
      "dropoff":{
        "address":{
          "@context":"/api/contexts/Address",
          "@id":"@string@.startsWith('/api/addresses')",
          "@type":"http://schema.org/Place",
          "geo":{
            "latitude":@double@,
            "longitude":@double@
          },
          "streetAddress":@string@,
          "telephone":null,
          "name":null
        }
      },
      "color":@string@
    }
    """

  Scenario: Create delivery with implicit pickup
    Given the database is empty
    And the fixtures file "stores.yml" is loaded
    And the user "bob" is loaded:
      | email      | bob@coopcycle.org |
      | password   | 123456            |
    And the store with name "Acme" belongs to user "bob"
    And the store with name "Acme" has token "abc123456789"
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Authorization" header equal to "Bearer abc123456789"
    And I send a "POST" request to "/api/deliveries" with body:
      """
      {
        "pickup": {
          "doneBefore": "tomorrow 13:00"
        },
        "dropoff": {
          "address": "48, Rue de Rivoli",
          "doneBefore": "tomorrow 13:30"
        }
      }
      """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should match:
    """
    {
      "@context":"/api/contexts/Delivery",
      "@id":"@string@.startsWith('/api/deliveries')",
      "@type":"http://schema.org/ParcelDelivery",
      "id":@integer@,
      "status":null,
      "pickup":{
        "address":{
          "@context":"/api/contexts/Address",
          "@id":"@string@.startsWith('/api/addresses')",
          "@type":"http://schema.org/Place",
          "geo":{
            "latitude":@double@,
            "longitude":@double@
          },
          "streetAddress":@string@,
          "telephone":null,
          "name":null
        }
      },
      "dropoff":{
        "address":{
          "@context":"/api/contexts/Address",
          "@id":"@string@.startsWith('/api/addresses')",
          "@type":"http://schema.org/Place",
          "geo":{
            "latitude":@double@,
            "longitude":@double@
          },
          "streetAddress":@string@,
          "telephone":null,
          "name":null
        }
      },
      "color":@string@
    }
    """
