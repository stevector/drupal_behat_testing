<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\ResultsServer
 *
 * PreProcesses DCI_ResultsServer variables, updating the job definition with a publish:drupalci_results: section.
 */

namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("resultsserver")
 */
class ResultsServer {

  /**
   * {@inheritdoc}
   *
   * DCI_ResultsServer_Preprocessor
   *
   * Takes a string defining the destination DCI results server to publish to,
   * and converts this to a 'publish:drupalci_results:' array as expected to
   * appear in a job definition.  Currenlty, this just points to an expected
   * configuration file defining the authentication parameters for that host,
   * but it could be extended to add the host auth information through
   * additional variables.
   *
   * Input format: (string) $value = "results.drupalci.org"
   * Desired Result: [
   * array('publish' => array('drupalci_results' => array(array('config' => '~/drupalci/drupalci.yaml'))))
   * ]
   */
  public function process(array &$definition, $value) {
    if (empty($definition['publish']['drupalci_results'])) {
      $definition['publish']['drupalci_results'] = [];
    }
    $definition['publish']['drupalci_results'][] = array('config' => '~/.drupalci/drupalci.yaml');
  }
}

