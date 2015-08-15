<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\ResultsServer
 *
 * PreProcesses DCI_ResultsServer variables, updating the job definition with
 * a publish:drupalci_results: section.
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
   * Input format: (string) $value = "https://user:pass@results.drupalci.org/"
   * Desired Result: [
   * array(
   *   'publish' => array(
   *     'drupalci_results' => array(
   *       array(
   *         'host' => 'results.drupalci.org',
   *         ['username' => 'user',]
   *         ['password' => 'pass']
   *
   *       )
   *     )
   *   )
   * )
   * ]
   */
  public function process(array &$definition, $url, $dci_variables) {
    if (empty($definition['publish']['drupalci_results'])) {
      $definition['publish']['drupalci_results'] = [];
    }
    $parsed = parse_url($url);
    $server = array();
    $server['host'] = $parsed['host'];
    if (!empty($parsed['scheme'])) {
      $server['host'] = $parsed['scheme'] . "://" . $server['host'];
    }
    if (!empty($parsed['user'])) {
      $server['username'] = $parsed['user'];
    }
    if (!empty($parsed['pass'])) {
      $server['password'] = $parsed['pass'];
    }

    $definition['publish']['drupalci_results'][] = $server;
  }
}

