<?php

/**
 * @file
 * Contains custom definitions for Workbench Moderation.
 */

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  /**
   * Creates a state with the provided information.
   *
   * @Given a state named ":label" with machine name ":name" exists
   */
  public function createState($label, $name = '') {
    if (!$name) {
      $name = str_replace(' ', '_', strtolower($label));
    }

    $this->stateCreate($label, $name);
  }

  /**
   * Creates a Moderation state if it does not exist.
   *
   * Note: Existence is only checked against the machine name, not the label.
   *
   * @param string $label
   *   The human-readable label of the state to create.
   * @param string $machine_name
   *   The machine name of the state to create.
   */
  public function stateCreate($label, $machine_name) {
    $storage = Drupal::entityManager()->getStorage('moderation_state');

    // If the state already exists, cool, don't do anything more.
    if ($storage->load($machine_name)) {
      return;
    }

    $state = $storage->create([
      'label' => $label,
      'id' => $machine_name,
    ]);
    $result = $state->save();

    if ($result != SAVED_NEW) {
      throw new \RunTimeException(sprintf('Could not create new role: %s', $label));
    }
  }
}
