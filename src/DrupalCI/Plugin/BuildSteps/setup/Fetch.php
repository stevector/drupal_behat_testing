<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\setup\Fetch
 *
 * Processes "setup: fetch:" instructions from within a job definition.
 */

namespace DrupalCI\Plugin\BuildSteps\setup;

use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use Guzzle\Http\Client;

/**
 * @PluginID("fetch")
 */
class Fetch extends SetupBase {

  /**
   * @var \Guzzle\Http\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    // Data format:
    // i) array('url' => '...', 'fetch_dir' => '...')
    // or
    // iii) array(array(...), array(...))
    // Normalize data to the third format, if necessary
    $data = (count($data) == count($data, COUNT_RECURSIVE)) ? [$data] : $data;
    Output::writeLn("<info>Entering setup_fetch().</info>");
    foreach ($data as $details) {
      // URL and target directory
      // TODO: Ensure $details contains all required parameters
      if (empty($details['url'])) {
        Output::error("Fetch error", "No valid target file provided for fetch command.");
        $job->error();
        return;
      }
      $url = $details['url'];
      $workingdir = $job->getJobCodebase()->getWorkingDir();
      $fetchdir = (!empty($details['fetch_directory'])) ? $details['fetch_directory'] : $workingdir;
      if (!($directory = $this->validateDirectory($job, $fetchdir))) {
        // Invalid checkout directory
        Output:error("Fetch error", "The fetch directory <info>$directory</info> is invalid.");
        $job->error();
        return;
      }
      $info = pathinfo($url);
      try {
        $destination_file = $directory . "/" . $info['basename'];
        $this->httpClient()
          ->get($url)
          ->setResponseBody($destination_file)
          ->send();
      }
      catch (\Exception $e) {
        Output::error("Write error", "An error was encountered while attempting to write <info>$url</info> to <info>$directory</info>");
        $job->error();
        return;
      }
      Output::writeLn("<comment>Fetch of <options=bold>$url</options=bold> to <options=bold>$destination_file</options=bold> complete.</comment>");
    }
  }

  /**
   * @return \Guzzle\Http\ClientInterface
   */
  protected function httpClient() {
    if (!isset($this->httpClient)) {
      $this->httpClient = new Client;
    }
    return $this->httpClient;
  }

}
