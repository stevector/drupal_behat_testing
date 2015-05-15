<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\configure\PrepResults
 *
 * Prepares the results site to publish job results
 */

namespace DrupalCI\Plugin\BuildSteps\configure;
use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginBase;
use DrupalCIResultsApi\Api;
use Symfony\Component\Yaml\Yaml;

/**
 * @PluginID("prepare_results_placeholders")
 */
class PrepResults extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data = NULL) {
    // Retrieve job definition array
    $definition = $job->getDefinition();
    // We only need to prep the results site if there is a publish['drupalci_results'] build step.
    if (empty($definition['publish']['drupalci_results'])) {
      return;
    }

    $data = $definition['publish']['drupalci_results'];
    $api = $job->getResultsAPI();

    // $config data format:
    // i) array('config' => '<configuration filename>'),
    // ii) array('host' => '...', 'username' => '...', 'password' => '...')
    // or a mixed array of the above
    // iii) array(array(...), array(...))
    // Normalize data to the third format, if necessary
    $data = (count($data) == count($data, COUNT_RECURSIVE)) ? [$data] : $data;
    foreach ($data as $key => $instance) {
      if (!empty($instance['config'])) {
        $config = $this->loadConfig($instance['config']);
      }
      else {
        $config['results'] = $instance;
      }
      $api->setUrl($config['results']['host']);
      $api->setAuth($config['results']['username'], $config['results']['password']);

      // TODO: We need to generate readable job titles.  Using $job->BuildID for now.
      $title = $job->getBuildID();

      // Generate the results node on the results server
      $host = parse_url($config['results']['host'], PHP_URL_HOST);
      $results_id = $job->getResultsServerID();
      $results_id[$host] = $api->create($title);
      // Store the result server record id on the job for future use
      $job->setResultsServerID($results_id);
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
