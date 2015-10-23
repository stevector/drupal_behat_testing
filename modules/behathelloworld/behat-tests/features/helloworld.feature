 Feature: Hello World

    Scenario: Drupal on the homepage
      Given I am an anonymous user
      And I visit "/"
      Then I should see "Drupal"
