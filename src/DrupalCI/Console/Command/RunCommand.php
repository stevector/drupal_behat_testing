<?php

/**
 * @file
 * Command class for Run.
 */

namespace DrupalCI\Console\Command;

use DrupalCI\Console\Helpers\ConfigHelper;
use DrupalCI\Console\Output;
use DrupalCI\Job\CodeBase\JobCodeBase;
use DrupalCI\Job\Definition\JobDefinition;
use DrupalCI\Job\Results\JobResults;
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
   */
  protected function configure() {
    $this
      ->setName('run')
      ->setDescription('Execute a given job run.')
      // Argument may be the job type or a specific job definition file
      ->addArgument('definition', InputArgument::OPTIONAL, 'Job definition.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output) {
    $arg = $input->getArgument('definition');

    $config_helper = new ConfigHelper();
    $local_overrides = $config_helper->getCurrentConfigSetParsed();

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

    // Create our job Codebase object and attach it to the job.
    $job_codebase = new JobCodebase();
    $job->setJobCodebase($job_codebase);

    // Create our job Definition object and attach it to the job.
    $job_definition = new JobDefinition();
    $job->setJobDefinition($job_definition);

    // Compile our complete list of DCI_* variables
    $job_definition->compile($job);

    // Setup our project and version metadata
    $job_codebase->setupProject($job_definition);

    // Determine the job definition template to be used
    if ($arg && strtolower(substr(trim($arg), -4)) == ".yml") {
      $template_file = $arg;
    }
    else {
      $template_file = $job->getDefaultDefinitionTemplate($job_type);
    }

    Output::writeLn("<info>Using job definition template: <options=bold>$template_file</options=bold></info>");

    // Load our job template file into the job definition.  If $template_file
    // doesn't exist, this will trigger a FileNotFound or ParseError exception.
    $job_definition->loadTemplateFile($template_file);

    // Process the complete job definition, taking into account DCI_* variable
    // and definition preprocessors, along with job-specific arguments
    $job_definition->preprocess($job);

    // Validate the resulting job definition, to ensure all required parameters
    // are present.
    $result = $job_definition->validate($job);
    if (!$result) {
      // Job definition failed validation.  Error output has already been
      // generated and displayed during execution of the validation method.
      return;
    }

    // Set up the local working directory
    $result = $job_codebase->setupWorkingDirectory($job_definition);
    if ($result === FALSE) {
      // Error encountered while setting up the working directory. Error output
      // has already been generated and displayed during execution of the
      // setupWorkingDirectory method.
      return;
    }

    // Create our job Results object and attach it to the job.
    $job_results = new JobResults($job);
    $job->setJobResults($job_results);

    // The job should now have a fully merged job definition file, including
    // any local or DrupalCI defaults not otherwise defined in the passed job
    // definition
    $definition = $job_definition->getDefinition();

    // Iterate over the build stages
    foreach ($definition as $build_stage => $steps) {
      if (empty($steps)) {
        $job_results->updateStageStatus($build_stage, 'Skipped');
        continue;
      }
      $job_results->updateStageStatus($build_stage, 'Executing');

      // Iterate over the build steps
      foreach ($steps as $build_step => $data) {
        $job_results->updateStepStatus($build_stage, $build_step, 'Executing');
        // Execute the build step
        $this->buildStepsPluginManager()->getPlugin($build_stage, $build_step)->run($job, $data);

        // Check for errors / failures after build step execution
        $status = $job_results->getResultByStep($build_stage, $build_step);
        if ($status == 'Error') {
          // Step returned an error.  Halt execution.
          Output::error("Execution Error", "Error encountered while executing job build step <options=bold>$build_stage:$build_step</options=bold>");
          break 2;
        }
        if ($status == 'Fail') {
          // Step returned an failure.  Halt execution.
          Output::error("Execution Failure", "Build step <options=bold>$build_stage:$build_step</options=bold> FAILED");
          break 2;
        }
        $job_results->updateStepStatus($build_stage, $build_step, 'Completed');
      }
      $job_results->updateStageStatus($build_stage, 'Completed');
    }
    // TODO: Gather results.
    // This should be moved out of the 'build steps' logic, as an error in any
    // build step halts execution of the entire loop, and the artifacts are not
    // processed.

  }

  /**
   * @return \DrupalCI\Plugin\PluginManagerInterface
   */
  protected function buildStepsPluginManager() {
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
