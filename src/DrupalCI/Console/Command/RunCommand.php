<?php

/**
 * @file
 * Command class for run.
 */

namespace DrupalCI\Console\Command;

use DrupalCI\Console\Helpers\ConfigHelper;
use DrupalCI\Console\Output;
use DrupalCI\Job\CodeBase\JobCodeBase;
use DrupalCI\Job\Definition\JobDefinition;
use DrupalCI\Plugin\PluginManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RunCommand extends DrupalCICommandBase {

  /**
   * @var \DrupalCI\Plugin\PluginManagerInterface
   */
  protected $buildStepsPluginManager;

  /**
   * @var \DrupalCI\Plugin\PluginManagerInterface
   */
  protected $jobPluginManager;

  /**
   * {@inheritdoc}
   *
   * Options:
   *   Will probably be a combination of things taken from environment variables
   *   and job specific options.
   *   TODO: Sort out how to define job-specific options, and be able to import
   *   them into the DrupalCI command. (Imported from a specially named file in
   *   the job directory, perhaps?) Will need syntax to define required versus
   *   optional options, and their defaults if not specified.
   */
  protected function configure() {
    $this
      ->setName('run')
      ->setDescription('Execute a given job run.')
      ->addArgument('definition', InputArgument::OPTIONAL, 'Job definition.');
    // TODO: Add 'definition file name' as an option
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output) {
    $arg = $input->getArgument('definition');

    $confighelper = new ConfigHelper();
    $local_overrides = $confighelper->getCurrentConfigSetParsed();

    // Determine the Job Type based on the first argument to the run command
    if ($arg) {
      $job_type = (strtolower(substr(trim($arg), -4)) == ".yml") ? "generic" : trim($arg);
    }
    else {
      // If no argument defined, then check for a default in the local overrides
      $job_type = (!empty($local_overrides['DCI_JobType'])) ? $local_overrides['DCI_JobType'] : 'generic';
    }

    // Load the associated class for this job type
    /** @var $job \DrupalCI\Plugin\JobTypes\JobInterface */
    $job = $this->jobPluginManager()->getPlugin($job_type, $job_type);

    // Link our $output variable to the job, so that jobs can display their work.
    Output::setOutput($output);

    // Generate a unique job build_id, and store it within the job object
    $job->generateBuildId();

    // Determine the job definition template to be used
    if ($arg && strtolower(substr(trim($arg), -4)) == ".yml") {
      $template_file = $arg;
    }
    else {
      $template_file = $job->getDefaultDefinitionTemplate($job_type);
    }

    Output::writeLn("<info>Using job definition template: <options=bold>$template_file</options=bold></info>");

    // Create a new job definition object for this job.  If $template_file does
    // not exist, this will trigger a FileNotFound or ParseError exception.
    $job_definition = new JobDefinition($template_file);

    // Compile the complete job definition, taking into account DCI_* variables
    // and job-specific arguments
    $job_definition->compile($job);
    $result = $job_definition->validate($job);
    if (!$result) {
      // Job definition failed validation.  Error output has already been
      // generated and displayed during execution of the validation method.
      return;
    }

    // Attach our job definition object to the job.
    $job->setJobDefinition($job_definition);

    // Create our job Codebase object and attach it to the job.
    $job_codebase = new JobCodebase($job);

    // Set up the local working directory
    $result = $job_codebase->setupWorkingDirectory($job_definition);
    if ($result === FALSE) {
      // Error encountered while setting up the working directory. Error output
      // has already been generated and displayed during execution of the
      // setupWorkingDirectory method.
      return;
    }

    // The job should now have a fully merged job definition file, including
    // any local or DrupalCI defaults not otherwise defined in the passed job
    // definition
    $definition = $job_definition->getDefinition();

    if (!empty($definition['publish']['drupalci_results'])) {
      $results_data = $job_definition['publish']['drupalci_results'];
      // $data format:
      // i) array('config' => '<configuration filename>'),
      // ii) array('host' => '...', 'username' => '...', 'password' => '...')
      // or a mixed array of the above
      // iii) array(array(...), array(...))
      // Normalize data to the third format, if necessary
      $results_data = (count($results_data) == count($results_data, COUNT_RECURSIVE)) ? [$results_data] : $results_data;
    }
    else {
      $results_data = array();
    }

    foreach ($definition as $build_step => $step) {
      if (empty($step)) { continue; }
      // If we are publishing this job to a results server (or multiple), update the progress on the server(s)
      // TODO: Check current state, and don't progress if already there.
      foreach ($results_data as $key => $instance) {
        $job->configureResultsAPI($instance);
        $api = $job->getResultsAPI();
        $url = $api->getUrl();
        // Retrieve the results node ID for the results server
        $host = parse_url($url, PHP_URL_HOST);
        $states = $api->states();
        $results_id = $job->getResultsServerID();

        foreach ($states as $key => $state) {
          if ($build_step == $key) {
            $api->progress($results_id[$host], $state['id']);
            break;
          }
        }
      }

      foreach ($step as $plugin => $data) {
        $this->buildstepsPluginManager()->getPlugin($build_step, $plugin)->run($job, $data);
        if ($job->getErrorState()) {
          // Step returned an error.  Halt execution.
          // TODO: Graceful handling of early exit states.
          $output->writeln("<error>Job halted.</error>");
          $output->writeln("<comment>Exiting job due to an invalid return code during job build step: <options=bold>'$build_step=>$plugin'</options=bold></comment>");
          break 2;
        }
      }
    }
  }

  /**
   * @return \DrupalCI\Plugin\PluginManagerInterface
   */
  protected function buildstepsPluginManager() {
    if (!isset($this->buildStepsPluginManager)) {
      $this->buildStepsPluginManager = new PluginManager('BuildSteps');
    }
    return $this->buildStepsPluginManager;
  }

    /**
   * @return \DrupalCI\Plugin\PluginManagerInterface
   */
  protected function jobPluginManager() {
    if (!isset($this->jobPluginManager)) {
      $this->jobPluginManager = new PluginManager('JobTypes');
    }
    return $this->jobPluginManager;
  }

}
