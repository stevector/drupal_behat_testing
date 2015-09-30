<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\SyntaxCheck
 *
 * PreProcesses DCI_SyntaxCheck variable.
 */

namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("syntaxcheck")
 */
class SyntaxCheck {

  /**
   * {@inheritdoc}
   *
   * DCI_SyntaxCheck_Preprocessor
   *
   * If set, run php lint on committed code.
   *
   * Input format: (bool) $value = "true"
   */
  public function process(array &$definition, $value) {
    // Make sure our syntax check runs last.
    unset($definition['setup']['syntaxcheck']);
    $definition['setup']['syntaxcheck'] = $value ? "TRUE" : "FALSE";
  }
}
