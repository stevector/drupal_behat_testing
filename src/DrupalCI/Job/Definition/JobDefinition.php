<?php

/**
 * @file
 * Contains \DrupalCI\Job\Definition\JobDefinition.
 */

// TODO: This class does not appear to ever be called

namespace DrupalCI\Job\Definition;

use DrupalCI\Console\Helpers\ConfigHelper;
use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginManager;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class JobDefinition {

  // Location of our job definition template
  protected $template_file;
  protected function setTemplateFile($template_file) {  $this->template_file = $template_file; }

  // Contains the parsed job definition
  protected $definition = array();
  public function getDefinition() {  return $this->definition;  }
  public function setDefinition(array $job_definition) {  $this->definition = $job_definition;  }

  /**
   * @var \DrupalCI\Plugin\PluginManager;
   */
  protected $pluginManager;


  function __construct($template_file) {
    // Store the template location
    $this->setTemplateFile($template_file);

    // Get and parse the default definition template (containing %DCI_*%
    // placeholders) into the job definition.

    // For 'generic' jobs, this is either the file passed in on the
    // 'drupalci run <filename>' command; and should be fully populated (though
    // template placeholders *can* be supported) ... or a drupalci.yml file at
    // the working directory root.

    // For other 'jobtype' jobs, this is the file location returned by
    // the $job->getDefaultDefinitionTemplate() method, which defaults to
    // DrupalCI/Plugin/JobTypes/<jobtype>/drupalci.yml for most job types.
    if (!file_exists($template_file)) {
      //Output::writeln("Unable to locate job definition template at <options=bold>$template_file</options=bold>");
      throw new FileNotFoundException("Unable to locate job definition template at $template_file.");
    }

    // Attempt to parse the job definition template and save it to our definition variable.
    // The YAML class will throw an exception if this fails.
    $this->setDefinition($this->loadYaml($template_file));
  }

  /**
   * Compile the complete job definition
   *
   * Populates the job definition template based on DCI_* variables and
   * job-specific arguments
   */
  public function compile(JobInterface $job) {
    // Compile our list of DCI_* variables
    $dci_variables = $this->compileDciVariables($job);

    // Execute variable preprocessor plugin logic
    $this->executeVariablePreprocessors($dci_variables);

    // Execute definition preprocessor plugin logic
    $this->executeDefinitionPreprocessors($dci_variables);

    // Process DCI_* variable substitution into the job definition template
    $this->substituteVariables($dci_variables);

    // Add the build variables and job definition to our job object, for
    // compatibility.
    // TODO: References to these on the job should be moved over to reference
    // the job definition instead.
    $job->setBuildVars($dci_variables + $job->getBuildVars());
    $job->setDefinition($this->getDefinition());

  }

  /**
   * Given a file, returns an array containing the parsed YAML contents from that file
   *
   * @param $source
   *   A YAML source file
   * @return array
   *   an array containing the parsed YAML contents from the source file
   * @throws ParseException
   */
  protected function loadYaml($source) {
    if ($content = file_get_contents($source)) {
      return Yaml::parse($content);
    }
    throw new ParseException("Unable to parse empty job definition template file at $source.");
  }

  /**
   * Compiles the list of available DCI_* variables to consider with this job
   */
  protected function compileDciVariables(JobInterface $job) {
    // Get and parse external (i.e. anything not from the default definition
    // file) job argument parameters.  DrupalCI jobs are controlled via a
    // hierarchy of configuration settings, which define the behaviour of the
    // platform while running DrupalCI jobs.  This hierarchy is defined as
    // follows, which each level overriding the previous:

    // 1. Out-of-the-box DrupalCI platform defaults, as defined in DrupalCI/Plugin/JobTypes/JobBase->platformDefaults
    $platform_defaults = $job->getPlatformDefaults();
    if (!empty($platform_defaults)) {
      Output::writeLn("<comment>Loading DrupalCI platform default arguments:</comment>");
      Output::writeLn(implode(",", array_keys($platform_defaults)));
    }

    // 2. Out-of-the-box DrupalCI JobType defaults, as defined in DrupalCI/Plugin/JobTypes/<jobtype>->defaultArguments
    $jobtype_defaults = $job->getDefaultArguments();
    if (!empty($jobtype_defaults)) {
      Output::writeLn("<comment>Loading job type default arguments:</comment>");
      Output::writeLn(implode(",", array_keys($jobtype_defaults)));
    }

    // 3. Local overrides defined in ~/.drupalci/config
    $confighelper = new ConfigHelper();
    $local_overrides = $confighelper->getCurrentConfigSetParsed();
    if (!empty($local_overrides)) {
      Output::writeLn("<comment>Loading local DrupalCI environment config override arguments.</comment>");
      Output::writeLn(implode(",", array_keys($local_overrides)));
    }

    // 4. 'DCI_' namespaced environment variable overrides
    $environment_variables = $confighelper->getCurrentEnvVars();
    if (!empty($environment_variables)) {
      Output::writeLn("<comment>Loading local namespaced environment variable override arguments.</comment>");
      Output::writeLn(implode(",", array_keys($environment_variables)));
    }

    // 5. Additional variables passed in via the command line
    // TODO: Not yet implemented
    $cli_variables = ['DCI_JobBuildId' => $job->getBuildId()];

    // Combine the above to generate the final array of DCI_* key=>value pairs
    $dci_variables = $cli_variables + $environment_variables + $local_overrides + $jobtype_defaults + $platform_defaults;

    // Reorder array, placing priority variables at the front
    if (!empty($job->priorityArguments)) {
      $original_array = $dci_variables;
      $original_keys = array_keys($original_array);
      $ordered_variables = [];
      foreach ($job->priorityArguments as $element) {
        if (in_array($element, $original_keys)) {
          $ordered_variables[$element] = $original_array[$element];
          unset($original_array[$element]);
        }
      }
      $dci_variables = array_merge($ordered_variables, $original_array);
    }

    return $dci_variables;
  }

  /**
   * Execute Variable preprocessor Plugin logic
   */
  protected function executeVariablePreprocessors(&$dci_variables) {
    // For each DCI_* element in the array, check to see if a variable
    // preprocessor exists, and process it if it does.
    $replacements = [];
    $plugin_manager = $this->getPreprocessPluginManager();
    foreach ($dci_variables as $key => &$value) {
      if (preg_match('/^DCI_(.+)$/i', $key, $matches)) {
        $name = strtolower($matches[1]);
        if ($plugin_manager->hasPlugin('variable', $name)) {
          /** @var \DrupalCI\Plugin\Preprocess\VariableInterface $plugin */
          $plugin = $plugin_manager->getPlugin('variable', $name);
          // @TODO: perhaps this should be on the annotation.
          $new_keys = $plugin->target();
          if (!is_array($new_keys)) {
            $new_keys = [$new_keys];
          }
          // @TODO: error handling.
          foreach ($new_keys as $new_key) {
            // Only process variable plugins if the variable being changed actually exists.
            if (!empty($dci_variables[$new_key])) {
              $dci_variables[$new_key] = $plugin->process($dci_variables[$new_key], $value, $new_key);
            }
          }
        }
      }
    }
  }

  /**
   * Execute Variable preprocessor Plugin logic
   */
  protected function executeDefinitionPreprocessors($dci_variables) {
    $definition = $this->getDefinition();
    $plugin_manager = $this->getPreprocessPluginManager();
    // Foreach DCI_* pair in the array, check if a definition plugin exists,
    // and process if it does.  We pass in the test definition template and
    // complete array of DCI_* variables.
    foreach ($dci_variables as $key => $value) {
      if (preg_match('/^DCI_(.+)$/', $key, $matches)) {
        $name = strtolower($matches[1]);
        if ($plugin_manager->hasPlugin('definition', $name)) {
          $plugin_manager->getPlugin('definition', $name)
            ->process($definition, $value, $dci_variables);
        }
      }
    }
    $this->setDefinition($definition);
  }

  /**
   * Substitute DCI_* variables into the job definition template
   */
  protected function substituteVariables($dci_variables) {
    // Generate our replacements array
    $replacements = [];
    foreach ($dci_variables as $key => $value) {
      if (preg_match('/^DCI_(.+)$/', $key, $matches)) {
        $name = strtolower($matches[1]);
        $replacements["%$key%"] = $value;
      }
    }

    // Add support for substituting '%HOME%' with the $HOME env variable
    $replacements["%HOME%"] = getenv("HOME");

    // Process DCI_* variable substitution into test definition template
    $search = array_keys($replacements);
    $replace = array_values($replacements);
    $definition = $this->getDefinition();
    array_walk_recursive($definition, function (&$value) use ($search, $replace) {
      $value = str_ireplace($search, $replace, $value);
    });

    // Save our post-replacements job definition back to the object
    $this->setDefinition($definition);

  }

  /**
   * @return \DrupalCI\Plugin\PluginManager
   */
  protected function getPreprocessPluginManager() {
    if (!isset($this->pluginManager)) {
      $this->pluginManager = new PluginManager('Preprocess');
    }
    return $this->pluginManager;
  }



  // Other potential methods for this class:
  // insert build step before/after
  // get/set DCI_parameters

}