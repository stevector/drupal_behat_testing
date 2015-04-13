<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\publish\PublishToDrupalCIServer
 *
 * Processes "publish: drupalci_server:" instructions from within a job
 * definition. Gathers the resulting job artifacts and pushes them to a
 * DrupalCI Results server.
 */

namespace DrupalCI\Plugin\BuildSteps\publish;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginBase;
use DrupalCIResultsApi\Api;

/**
 * @PluginID("drupalci_results")
 */
class PublishToDrupalCIServer extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    // Data format:
    // i) array('config' => '<configuration filename>'),
    // ii) array('host' => '...', 'username' => '...', 'password' => '...')
    // or a mixed array of the above
    // iii) array(array(...), array(...))
    // Normalize data to the third format, if necessary
    $data = (count($data) == count($data, COUNT_RECURSIVE)) ? [$data] : $data;
    $api = new API();
    $config = array();
    foreach ($data as $key => $instance) {
      if (!empty($instance['config'])) {
        $config = $this->loadConfig($instance['config']);
      }
      else {
        $config['results'] = $instance;
      }
      $api->setUrl($config['results']['host']);
      $api->setAuth($config['results']['username'], $config['results']['password']);

      // Retrieve the results id
      $results_id = $job->getResultServerId();

      // Collect build artifacts

      // Generate summary string
      // $api->summary($results_id, $summary);

      // Publish build artifacts
      // $api->artefacts($results_id, $artifacts);
      // TODO: Correct spelling in API implementation

      // Update state to completed
      // $api->progress($results_id, $state);
    }
  }

  protected function loadConfig($config_filename) {
    if (!$config = $this->loadYaml($config_filename)) {
      // TODO: Throw Exception

    }
    return $config;
  }

}
