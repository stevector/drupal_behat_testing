<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\ResultsServerConfig
 *
 * PreProcesses DCI_ResultsServerConfig variables, updating the job definition
 * with a publish:drupalci_results: section.
 */

namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("resultsserverconfig")
 */
class ResultsServerConfig {

  /**
   * {@inheritdoc}
   *
   * DCI_ResultsServerConfig_Preprocessor
   *
   * Takes a string defining the destination DCI results server configuration
   * file, and converts this to a 'publish:drupalci_results:config:' entry as
   * expected to appear in a job definition.  Currently, this just points to
   * an expected configuration file defining the authentication parameters for
   * that host, but it could be extended to add the host auth information
   * through additional variables.
   *
   * Input format: (string) $value = "%HOME%/drupalci/drupalci.yaml""
   * Desired Result: [
   * array(
   *   'publish' => array(
   *     'drupalci_results' => array(
   *       array(
   *         'config' => '%HOME%/drupalci/drupalci.yaml',
   *       )
   *     )
   *   )
   * )
   * ]
   */
  public function process(array &$definition, $value, $dci_variables) {
    if (empty($definition['publish']['drupalci_results'])) {
      $definition['publish']['drupalci_results'] = [];
    }
    $definition['publish']['drupalci_results'][] = array('config' => $value);
  }
}

