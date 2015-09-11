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
    $definition = $job->getJobDefinition->getDefinition();
    // We only need to prep the results site if there is a publish['drupalci_results'] build step.
    if (empty($definition['publish']['drupalci_results'])) {
      return;
    }

    // The results node could be defined further upstream, and passed in to us
    // in an environment variable (DCI_JobID);
    $upstream_id = $job->getBuildVar('DCI_JobID');
    // If we have a job ID, we also need a results server URL or config file location.
    $results_server = $job->getBuildVar('DCI_ResultsServer');
    $results_server_config = $job->getBuildVar('DCI_ResultsServerConfig');
    if (!empty($upstream_id)) {
      // Job ID found.  Results node is assumed to already have been created.
      if (!empty($results_server_config)) {
        // If passed DCI_ResultsServerConfig, we need to load up information
        // from the specified configuration file (which should exist locally)
        $config = $this->loadConfig($results_server_config);
        $host = parse_url($config['results']['host'], PHP_URL_HOST);
      }
      elseif (!empty($results_server)) {
        // If passed DCI_ResultsServer, we need to parse the host out of the
        // provided URL.
        $host = parse_url($results_server, PHP_URL_HOST);
      }
      if (empty($host)) {
        // If unable to parse a ResultsServer host out of either variable, then
        // output a warning message.
        Output::writeln('<warning>Unable to determine destination DrupalCI results server. Job results will not be published.</warning>');
        return;
      }
      // Add the Results Node location to our job object so it can be
      // referenced by the publish:drupalci_results: plugin.
      $results = $job->getResultsServerID();
      $results[$host] = $upstream_id;
      $job->setResultsServerID($results);
      return;
    }

    // If we get to this point, we don't have an upstream results server or
    // node specified, so we'll generate it here.
    $this->generateResultNode($job, $definition);
  }

  /**
   * @param $source filename
   *   A local source file containing Results Server connectivity information
   * @return array
   *   Array of Results Server information
   */
  protected function loadConfig($source) {
    $config = array();
    $source = str_replace('%HOME%', getenv('HOME'), $source);
    if ($content = file_get_contents($source)) {
      $parsed = Yaml::parse($content);
      $config['results']['host'] = $parsed['results']['host'];
      $config['results']['username'] = $parsed['results']['username'];
      $config['results']['password'] = $parsed['results']['password'];
    }
    return $config;
  }

  protected function generateResultNode(JobInterface $job, $definition) {
    $data = $definition['publish']['drupalci_results'];
    // $config data format:
    // i) array('config' => '<configuration filename>'),
    // ii) array('host' => '...', 'username' => '...', 'password' => '...')
    // or a mixed array of the above
    // iii) array(array(...), array(...))
    // Normalize data to the third format, if necessary
    $data = (count($data) == count($data, COUNT_RECURSIVE)) ? [$data] : $data;
    foreach ($data as $key => $instance) {
      // TODO: We need to generate readable job titles.  Using $job->BuildID for now.
      $title = $job->getBuildId();
      $job->configureResultsAPI($instance);
      $api = $job->getResultsAPI();

      // Generate the results node on the results server
      $url = trim($api->getUrl(), '/');
      $host = parse_url($url, PHP_URL_HOST);
      $results_id = $job->getResultsServerID();
      $results_id[$host] = $api->create($title);

      // Store the result server record id on the job for future use
      $job->setResultsServerID($results_id);
    }
  }
}
