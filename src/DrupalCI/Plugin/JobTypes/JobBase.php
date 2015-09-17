<?php
/**
 * @file
 * Base Job class for DrupalCI.
 */

namespace DrupalCI\Plugin\JobTypes;

use Drupal\Component\Annotation\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use DrupalCI\Console\Output;
use DrupalCI\Job\Results\Artifacts\BuildArtifact;
use DrupalCI\Job\Results\Artifacts\BuildArtifactList;
use DrupalCI\Job\CodeBase\JobCodeBase;
use DrupalCI\Job\Definition\JobDefinition;
use DrupalCI\Job\Results\JobResults;
use DrupalCIResultsApi\Api;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tests\Output\ConsoleOutputTest;
use Symfony\Component\Process\Process;
use DrupalCI\Console\Jobs\ContainerBase;
use Docker\Docker;
use Docker\Http\DockerClient as Client;
use Symfony\Component\Yaml\Yaml;
use Docker\Container;
use PDO;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\ConsoleEvents;

class JobBase extends ContainerBase implements JobInterface {

  /**
   * Stores the job type
   *
   * @var string
   */
  protected $jobType = 'base';
  public function getJobType() {  return $this->jobType;  }

  /**
   * Stores the calling command's output buffer
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  public $output;
  public function setOutput(OutputInterface $output) {  $this->output = $output;  }
  public function getOutput() {  return $this->output;  }

  /**
   * Stores a build ID for this job
   *
   * @var string
   */
  protected $buildId;
  public function getBuildId() {  return $this->buildId;  }
  public function setBuildId($buildId) {  $this->buildId = $buildId;  }

  /**
   * Stores the job definition object for this job
   *
   * @var \DrupalCI\Job\Definition\JobDefinition
   */
  protected $jobDefinition = NULL;
  public function getJobDefinition() {  return $this->jobDefinition;  }
  public function setJobDefinition(JobDefinition $job_definition) {  $this->jobDefinition = $job_definition; }

  /**
   * Stores the codebase object for this job
   *
   * @var \DrupalCI\Job\CodeBase\JobCodebase
   */
  protected $jobCodebase;
  public function getJobCodebase() {  return $this->jobCodebase;  }
  public function setJobCodebase(JobCodeBase $job_codebase)  {  $this->jobCodebase = $job_codebase;  }

  /**
   * Stores the results object for this job
   *
   * @var \DrupalCI\Job\Results\JobResults
   */
  protected $jobResults;
  public function getJobResults() {  return $this->jobResults;  }
  public function setJobResults(JobResults $job_results)  {  $this->jobResults = $job_results;  }

  /**
   * Defines argument variable names which are valid for this job type
   *
   * @var array
   */
  protected $availableArguments = array();
  public function getAvailableArguments() {  return $this->availableArguments;  }

  /**
   * Defines the default arguments which are valid for this job type
   *
   * @var array
   */
  protected $defaultArguments = array();
  public function getDefaultArguments() {  return $this->defaultArguments;  }

  /**
   * Defines the required arguments which are necessary for this job type
   *
   * Format:  array('ENV_VARIABLE_NAME' => 'CONFIG_FILE_LOCATION'), where
   * CONFIG_FILE_LOCATION is a colon-separated nested location for the
   * equivalent variable in a job definition file.
   *
   * @var array
   */
  protected $requiredArguments = array();   // eg:   'DCI_DBVersion' => 'environment:db'
  public function getRequiredArguments() {  return $this->requiredArguments;  }

  /**
   * Defines initial platform defaults for all jobs (if not overridden).
   *
   * @var array
   */
  protected $platformDefaults = array(
    "DCI_CoreProject" => "Drupal",
    // DCI_WorkingDir defaults to a 'jobtype-buildID' directory in the system temp directory.
  );
  public function getPlatformDefaults() {  return $this->platformDefaults;  }

  /**
   * Stores build variables which need to be persisted between build steps
   *
   * @var array
   */
  protected $buildVars = array();
  public function getBuildVars() {  return $this->buildVars;  }
  public function setBuildVars(array $build_vars) {  $this->buildVars = $build_vars;  }
  public function getBuildVar($build_var) {  return isset($this->buildVars[$build_var]) ? $this->buildVars[$build_var] : NULL;  }
  public function setBuildVar($build_var, $value) {  $this->buildVars[$build_var] = $value;  }

  /**
   * Stores our Docker Container manager
   *
   * @var \Docker\Docker
   */
  protected $docker;

  /**
   * @return \Docker\Docker
   */
  public function getDocker()
  {
    $client = Client::createWithEnv();
    if (null === $this->docker) {
      $this->docker = new Docker($client);
    }
    return $this->docker;
  }







  /**
   * @var array
   */
  protected $pluginDefinitions;

  /**
   * @var array
   */
  protected $plugins;

  // Holds the name and Docker IDs of our service containers.
  public $serviceContainers;

  // Holds the name and Docker IDs of our executable containers.
  public $executableContainers = [];


  // Holds our DrupalCIResultsAPI API
  protected $resultsAPI = NULL;

  /**
   * @param API
   */
  public function setResultsAPI($resultsAPI)
  {
    $this->resultsAPI = $resultsAPI;
  }

  /**
   * @return API
   */
  public function getResultsAPI()
  {
    if (is_null($this->resultsAPI)) {
      $api = new API();
      $this->setResultsAPI($api);
    }
    return $this->resultsAPI;
  }

  public function configureResultsAPI($instance) {
    $api = $this->getResultsAPI();
    if (!empty($instance['config'])) {
      $config = $this->loadAPIConfig($instance['config']);
    }
    else {
      $config['results'] = $instance;
    }
    $api->setUrl($config['results']['host']);
    if (!empty($config['results']['username'])) {
      // Handle case where no password is provided
      if (empty($config['results']['password'])) {
        $config['results']['password'] = '';
      }
      // Set authorization parameters on the API object
      $api->setAuth($config['results']['username'], $config['results']['password']);
    }
    $this->setResultsAPI($api);
  }

  protected function loadAPIConfig($source) {
    $config = array();
    $source = realpath($source);
    if ($content = file_get_contents($source)) {
      $parsed = Yaml::parse($content);
      $config['results']['host'] = $parsed['results']['host'];
      $config['results']['username'] = $parsed['results']['username'];
      $config['results']['password'] = $parsed['results']['password'];
    }
    return $config;
  }

  // Stores a drupalci_results server node ID for this job
  public $resultsServerID;

  public function setResultsServerID($resultsServerID)
  {
    $this->resultsServerID = $resultsServerID;
  }

  /**
   * @return mixed
   */
  public function getResultsServerID()
  {
    return $this->resultsServerID;
  }




  public function getServiceContainers() {
    return $this->serviceContainers;
  }

  public function setServiceContainers(array $service_containers) {
    $this->serviceContainers = $service_containers;
  }

  public function error() {
    $results = $this->getJobResults();
    $stage = $results->getCurrentStage();
    $step = $results->getCurrentStep();
    $results->setResultByStage($stage, 'Error');
    $results->setResultByStep($stage, $step, 'Error');
  }

  public function fail() {
    $results = $this->getJobResults();
    $stage = $results->getCurrentStage();
    $step = $results->getCurrentStep();
    $results->setResultByStage($stage, 'Fail');
    $results->setResultByStep($stage, $step, 'Fail');
  }

  public function shellCommand($cmd) {
    $process = new Process($cmd);
    $process->setTimeout(3600*6);
    $process->setIdleTimeout(3600);
    $process->run(function ($type, $buffer) {
        Output::writeln($buffer);
    });
   }

  protected function discoverPlugins() {
    $dir = 'src/DrupalCI/Plugin';
    $plugin_definitions = [];
    foreach (new \DirectoryIterator($dir) as $file) {
      if ($file->isDir() && !$file->isDot()) {
        $plugin_type = $file->getFilename();
        $plugin_namespaces = ["DrupalCI\\Plugin\\$plugin_type" => ["$dir/$plugin_type"]];
        $discovery  = new AnnotatedClassDiscovery($plugin_namespaces, 'Drupal\Component\Annotation\PluginID');
        $plugin_definitions[$plugin_type] = $discovery->getDefinitions();
      }
    }
    return $plugin_definitions;
  }

  /**
   * @return \DrupalCI\Plugin\PluginBase
   */
  protected function getPlugin($type, $plugin_id, $configuration = []) {
    if (!isset($this->pluginDefinitions)) {
      $this->pluginDefinitions = $this->discoverPlugins();
    }
    if (!isset($this->plugins[$type][$plugin_id])) {
      if (isset($this->pluginDefinitions[$type][$plugin_id])) {
        $plugin_definition = $this->pluginDefinitions[$type][$plugin_id];
      }
      elseif (isset($this->pluginDefinitions['generic'][$plugin_id])) {
        $plugin_definition = $this->pluginDefinitions['generic'][$plugin_id];
      }
      else {
        throw new PluginNotFoundException("Plugin type $type plugin id $plugin_id not found.");
      }
      $this->plugins[$type][$plugin_id] = new $plugin_definition['class']($configuration, $plugin_id, $plugin_definition);
    }
    return $this->plugins[$type][$plugin_id];
  }


  public function getExecContainers() {
    $configs = $this->executableContainers;
    foreach ($configs as $type => $containers) {
      foreach ($containers as $key => $container) {
        // Check if container is created.  If not, create it
        if (empty($container['created'])) {
          // TODO: This may be causing duplicate containers to be created
          // due to a race condition during short-running exec calls.
          $this->startContainer($container);
          $this->executableContainers[$type][$key] = $container;
        }
      }
    }
    return $this->executableContainers;
  }

  public function setExecContainers(array $containers) {
    $this->executableContainers = $containers;
  }

  public function startContainer(&$container) {
    $docker = $this->getDocker();
    $manager = $docker->getContainerManager();
    // Get container configuration, which defines parameters such as exposed ports, etc.
    $configs = $this->getContainerConfiguration($container['image']);
    $config = $configs[$container['image']];
    // TODO: Allow classes to modify the default configuration before processing
    // Add service container links
    $this->createContainerLinks($config);
    // Add volumes
    $this->createContainerVolumes($config);
    // Set a default CMD in case the container config does not set one.
    if (empty($config['Cmd'])) {
      $this->setDefaultCommand($config);
    }

    $instance = new Container($config);
    $manager->create($instance);

    $manager->run($instance, function($output, $type) {
      fputs($type === 1 ? STDOUT : STDERR, $output);
    }, [], true);

    $container['id'] = $instance->getID();
    $container['name'] = $instance->getName();
    $container['created'] = TRUE;
    $short_id = substr($container['id'], 0, 8);
    Output::writeln("<comment>Container <options=bold>${container['name']}</options=bold> created from image <options=bold>${container['image']}</options=bold> with ID <options=bold>$short_id</options=bold></comment>");
  }

  protected function setDefaultCommand(&$config) {
    $config['Cmd'] = ['/bin/bash', '-c', '/daemon.sh'];
  }

  protected function createContainerLinks(&$config) {
    $links = array();
    if (empty($this->serviceContainers)) {
      return;
    }
    $targets = $this->serviceContainers;
    foreach ($targets as $type => $containers) {
      foreach ($containers as $key => $container) {
        $config['HostConfig']['Links'][] = "${container['name']}:${container['name']}";
      }
    }
  }

  protected function createContainerVolumes(&$config) {
    $volumes = array();
    // Map working directory
    $working = $this->getJobCodebase()->getWorkingDir();
    $mount_point = (empty($config['Mountpoint'])) ? "/data" : $config['Mountpoint'];
    $config['HostConfig']['Binds'][] = "$working:$mount_point";
  }

  public function getContainerConfiguration($image = NULL) {
    $path = __DIR__ . '/../../Containers';
    // RecursiveDirectoryIterator recurses into directories and returns an
    // iterator for each directory. RecursiveIteratorIterator then iterates over
    // each of the directory iterators, which consecutively return the files in
    // each directory.
    $directory = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
    $configs = [];
    foreach ($directory as $file) {
      if (!$file->isDir() && $file->isReadable() && $file->getExtension() === 'yml') {
        $container_name = $file->getBasename('.yml');
        $dev_suffix = '-dev';
        $isdev = strpos($container_name, $dev_suffix);
        if ( !$isdev == 0) {
          $container_name = str_replace('-dev', ':dev', $container_name);
        }
        $image_name = 'drupalci/' . $container_name;
        if (!empty($image) && $image_name != $image) {
          continue;
        }
        // Get the default configuration.
        $container_config = Yaml::parse(file_get_contents($file->getPathname()));
        $configs[$image_name] = $container_config;
      }
    }
    return $configs;
  }

  public function startServiceContainerDaemons($container_type) {
    $needs_sleep = FALSE;
    $docker = $this->getDocker();
    $manager = $docker->getContainerManager();
    $instances = array();
    foreach ($manager->findAll() as $running) {
      $repo = $running->getImage()->getRepository();
      $tag = $running->getImage()->getTag();
      $id = substr($running->getID(), 0, 8);
      $instance_key = !strcmp('latest',$tag) ? $repo : $repo . ':' . $tag;
      $instances[$instance_key] = $id;
    };
    foreach ($this->serviceContainers[$container_type] as $key => $image) {
      if (in_array($image['image'], array_keys($instances))) {
        // TODO: Determine service container ports, id, etc, and save it to the job.
        Output::writeln("<comment>Found existing <options=bold>${image['image']}</options=bold> service container instance.</comment>");
        // TODO: Load up container parameters
        $container = $manager->find($instances[$image['image']]);
        $container_id = $container->getID();
        $container_name = $container->getName();
        $container_ip = $container->getRuntimeInformations()["NetworkSettings"]["IPAddress"];
        $this->serviceContainers[$container_type][$key]['id'] = $container_id;
        $this->serviceContainers[$container_type][$key]['name'] = $container_name;
        $this->serviceContainers[$container_type][$key]['ip'] = $container_ip;
        continue;
      }
      // Container not running, so we'll need to create it.
      Output::writeln("<comment>No active <options=bold>${image['image']}</options=bold> service container instances found. Generating new service container.</comment>");

      // Get container configuration, which defines parameters such as exposed ports, etc.
      $configs = $this->getContainerConfiguration($image['image']);
      $config = $configs[$image['image']];
      // TODO: Allow classes to modify the default configuration before processing
      // Instantiate container
      $container = new Container($config);
      if (!empty($config['name'])) {
        $container->setName($config['name']);
      }
      // Create the docker container instance, running as a daemon.
      // TODO: Ensure there are no stopped containers with the same name (currently throws fatal)
      $manager->run($container, function($output, $type) {
        fputs($type === 1 ? STDOUT : STDERR, $output);
      }, [], true);
      $container_id = $container->getID();
      $container_name = $container->getName();
      $container_ip = $container->getRuntimeInformations()["NetworkSettings"]["IPAddress"];
      $this->serviceContainers[$container_type][$key]['id'] = $container_id;
      $this->serviceContainers[$container_type][$key]['name'] = $container_name;
      $this->serviceContainers[$container_type][$key]['ip'] = $container_ip;
      $short_id = substr($container_id, 0, 8);
      Output::writeln("<comment>Created new <options=bold>${image['image']}</options=bold> container instance with ID <options=bold>$short_id</options=bold></comment>");
    }

    $dburl_parts = parse_url($this->buildVars['DCI_DBUrl']);
    $dburl_parts['host'] = $container_ip;
    if(!strpos('sqlite', $dburl_parts['scheme'])){
      $counter = 0;
      $increment = 10;
      $max_sleep = 120;
      while($counter < $max_sleep ){
        if ($this->checkDBStatus($dburl_parts)){
          Output::writeln("<comment>Database is active.</comment>");
          break;
        }
        if ($counter >= $max_sleep){
          Output::writeln("<error>Max retries reached, exiting promgram.</error>");
          exit(1);
        }
        Output::writeln("<comment>Sleeping " . $increment . " seconds to allow service to start.</comment>");
        sleep($increment);
        $counter += $increment;

      }
    }
  }

  public function checkDBStatus($dburl_parts)
  {
    if(strcmp('mariadb',$dburl_parts['scheme']) === 1){
      $dburl_parts['scheme'] = 'mysql';
    }
    try {
      $conn_string = $dburl_parts['scheme'] . ':host=' . $dburl_parts['host'];
      Output::writeln("<comment>Attempting to connect to database server.</comment>");
      $conn = new PDO($conn_string, $dburl_parts['user'], $dburl_parts['pass']);
    } catch (\PDOException $e) {
      Output::writeln("<comment>Could not connect to database server.</comment>");
      return FALSE;
    }
    return TRUE;
  }

  public function getErrorState() {
    $results = $this->getJobResults();
    return ($results->getResultByStep($results->getCurrentStage(), $results->getCurrentStep()) === "Error");
  }

  public function getArtifactList($include = array()) {
    // Returns a list of build artifacts relevant to this job type.
    // Syntax: array(filename1, filename2, ...)
    $artifacts = array();

    // Artifacts common to all jobs:
    // - job definition
    $artifacts['definition'] = "results/job_definition.txt";

    // - standard output
    $artifacts['stdout'] = "results/stout.txt";

    // - standard error
    $artifacts['stderr'] = "results/sterr.txt";

    $artifacts = array_merge($artifacts, $include);

    $this->setArtifacts($artifacts);

    return $artifacts;
  }

  public $artifactFilename;

  /**
   * @param mixed $artifactFilename
   */
  public function setArtifactFilename($artifactFilename)
  {
    $this->artifactFilename = $artifactFilename;
  }

  /**
   * @return mixed
   */
  public function getArtifactFilename()
  {
    return $this->artifactFilename;
  }

  /**
   * @var /DrupalCI/Job/Artifacts/BuildArtifactList
   */
  protected $artifacts;
  public function setArtifacts($artifacts) { $this->artifacts = $artifacts; }
  public function getArtifacts() { return $this->artifacts; }

  public function __construct() {
    $this->createArtifactList();
  }

  protected function createArtifactList() {
    if (!isset($this->artifacts)) {
      $this->artifacts = New BuildArtifactList();
    }
    // Load the standard base build artifacts into the list
    foreach($this->defaultBuildArtifacts as $key => $value) {
      $artifact = New BuildArtifact('file', $value);
      $this->artifacts->addArtifact($key, $artifact);
    }
    // Load the jobType specific build artifacts into the list
    // Format: array(key, target, [type = file])
    foreach ($this->buildArtifacts as $value) {
      $key = $value[0];
      $target = $value[1];
      $type = isset($value[2]) ? $value[2] : 'file';
      $artifact = New BuildArtifact($type, $target);
      $this->artifacts->addArtifact($key, $artifact);
    }
  }

  // Provide the default file locations for standard build artifacts.
  protected $defaultBuildArtifacts = array(
    //'stdout' => 'stdout.txt',
    //'stderr' => 'stderr.txt',
    'jobDefinition' => 'jobDefinition.txt',
  );

  /**
   * Provide the details for job-specific build artifacts.
   *
   * This should be overridden by job-specific classes, to define the build
   * artifacts which should be collected for that class.
   *
   * The default build artifacts listed above can be overridden here as well.
   */
  protected $buildArtifacts = array(
    // e.g. phpunit results file at ./results.txt:
    // array('phpunit_results', './results.txt'),
    // e.g. multiple xml files within results/xml directory:
    // array('xml_results', 'results/xml', 'directory')
    // e.g. a string representing red/blue outcome:
    // array('color', 'red', 'string')
  );

  protected $artifactDirectory;

  /**
   * @param mixed $artifactDirectory
   */
  public function setArtifactDirectory($artifactDirectory)
  {
    $this->artifactDirectory = $artifactDirectory;
  }

  /**
   * @return mixed
   */
  public function getArtifactDirectory()
  {
    return $this->artifactDirectory;
  }

  /**
   * Returns the default job definition template for this job type
   *
   * This method may be overridden by a specific job class to add template
   * selection logic, if desired.
   *
   * @param $job_type
   *   The name of the job type, used to select the appropriate subdirectory
   *
   * @return string
   *   The location of the default job definition template
   */
  public function getDefaultDefinitionTemplate($job_type) {
    return __DIR__ . "/$job_type/drupalci.yml";
  }

  /**
   * Generate a Build ID for this job
   */
  public function generateBuildId() {
    // Use the BUILD_TAG environment variable if present, otherwise generate a
    // unique build tag based on timestamp.
    $build_id = getenv('BUILD_TAG');
    if (empty($build_id)) {
      $build_id = $this->getJobType() . '_' . time();
    }
    $this->setBuildId($build_id);
    Output::writeLn("<info>Executing job with build ID: <options=bold>$build_id</options=bold></info>");
  }

}
