<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\publish\DrupalCIResults
 *
 * Processes "publish: drupalci_server:" instructions from within a job
 * definition. Gathers the resulting job artifacts and pushes them to a
 * DrupalCI Results server.
 */

namespace DrupalCI\Plugin\BuildSteps\publish;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginBase;
use DrupalCIResultsApi\Api;
use Symfony\Component\Yaml\Yaml;

/**
 * @PluginID("drupalci_results")
 */
class DrupalCIResults extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    // $data format:
    // i) array('config' => '<configuration filename>'),
    // ii) array('host' => '...', 'username' => '...', 'password' => '...')
    // or a mixed array of the above
    // iii) array(array(...), array(...))
    // Normalize data to the third format, if necessary
    $data = (count($data) == count($data, COUNT_RECURSIVE)) ? [$data] : $data;

    $api = $job->getResultsAPI();

    foreach ($data as $key => $instance) {
      $job->configureResultsAPI($instance);
      $url = $api->getUrl();
      // Retrieve the results node ID for the results server
      $host = parse_url($url, PHP_URL_HOST);
      $results_id = $job->getResultsServerID();
      $states = $api->states();
      $api->progress($results_id[$host], $states['complete']['id']);
      // TODO: Calculate a summary message
      // For now, we just set a default message
      $api->summary($results_id[$host], "Test complete");
    }

  }

  protected function loadConfig($source) {
    $config = array();
    if ($content = file_get_contents($source)) {
      $parsed = Yaml::parse($content);
      $config['results']['host'] = $parsed['results']['host'];
      $config['results']['username'] = $parsed['results']['username'];
      $config['results']['password'] = $parsed['results']['password'];
    }
    return $config;
  }
}
