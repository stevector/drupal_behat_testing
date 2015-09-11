<?php
/**
 * @file
 * Contains
 */
namespace DrupalCI\Plugin\JobTypes;

use DrupalCI\Job\CodeBase\JobCodebase;
use DrupalCI\Job\Definition\JobDefinition;
use DrupalCI\Job\Results\JobResults;
use Symfony\Component\Console\Output\OutputInterface;

interface JobInterface {

  /**
   * @return string
   */
  public function getJobType();

  /**
   * @return \Symfony\Component\Console\Output\OutputInterface
   */
  public function getOutput();

  /**
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  public function setOutput(OutputInterface $output);

  /**
   * @return string
   */
  public function getBuildId();

  /**
   * @param string
   */
  public function setBuildId($id);

  /**
   * @return \DrupalCI\Job\Definition\JobDefinition
   */
  public function getJobDefinition();

  /**
   * @param \DrupalCI\Job\Definition\JobDefinition $job_definition
   */
  public function setJobDefinition(JobDefinition $job_definition);

  /**
   * @return \DrupalCI\Job\CodeBase\JobCodebase
   */
  public function getJobCodebase();

  /**
   * @param \DrupalCI\Job\CodeBase\JobCodebase $job_codebase
   */
  public function setJobCodebase(JobCodebase $job_codebase);

  /**
   * @return \DrupalCI\Job\Results\JobResults
   */
  public function getJobResults();

  /**
   * @param \DrupalCI\Job\Results\JobResults $job_results
   */
  public function setJobResults(JobResults $job_results);


  /**
   * Available arguments.
   *
   * @TODO: move to annotation
   *
   * @return array
   *
   * @see SimpletestJob::$availableArguments
   */
  public function getAvailableArguments();

  /**
   * Default arguments.
   *
   * @TODO: move to annotation
   *
   * @return array
   *
   * @see SimpletestJob::$defaultArguments
   */
  public function getDefaultArguments();

  /**
   * Required arguments.
   *
   * @TODO: move to annotation
   *
   * @return array
   *
   * @see SimpletestJob::$requiredArguments
   */
  public function getRequiredArguments();

  /**
   * An array of build variables.
   *
   * @return array
   *
   * @see SimpletestJob::$availableArguments
   */
  public function getBuildVars();

  /**
   * @param array $build_vars
   *
   * @see JobInterface::getBuildvars
   */
  public function setBuildVars(array $build_vars);

  /**
   * @param string $build_var
   *
   * @return mixed
   *
   * @see JobInterface::getBuildvars
   */
  public function getBuildVar($build_var);

  /**
   * @param $build_var
   * @param $value
   */
  public function setBuildVar($build_var, $value);

  /**
   * @return \Docker\Docker
   */
  public function getDocker();

  /**
   * Execute a shell command.
   *
   * @param string $cmd
   *   The commmand line.
   *
   * @see \Symfony\Component\Process\Process::__construct().
   */
  public function shellCommand($cmd);

  public function configureResultsAPI($config);
  /**
   * Get a list of containers to run Docker exec in.
   *
   * @return array
   *  An array of container IDs. The first key is the type, can be 'php' or
   *  'web'. Web has everything php plus Apache.
   */
  public function getExecContainers();

  public function setExecContainers(array $containers);

  public function startContainer(&$container);

  public function getContainerConfiguration($image = NULL);

  public function startServiceContainerDaemons($type);

  public function getErrorState();



  public function getPlatformDefaults();

  public function getServiceContainers();

  public function setServiceContainers(array $service_containers);


  public function setResultsServerID($id);

  public function getResultsServerID();

  /**
   * @return \DrupalCIResultsAPI\API
   */
  public function getResultsAPI();

  public function setResultsAPI($api);

  public function getArtifacts();

  public function setArtifacts($artifacts);

  public function getArtifactFilename();

  public function setArtifactFilename($filename);

  public function getArtifactDirectory();

  public function setArtifactDirectory($directory);

  public function getDefaultDefinitionTemplate($job_type);


  public function generateBuildId();

  public function error();

  public function fail();
}