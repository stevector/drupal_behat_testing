<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\configure\CompileDefinition
 *
 * Compiles a complete job definition from a hierarchy of sources.
 * This hierarchy is defined as follows, which each level overriding the previous:
 * 1. Out-of-the-box DrupalCI defaults
 * 2. Local overrides defined in ~/.drupalci/config
 * 3. 'DCI_' namespaced environment variable overrides
 * 4. Test-specific overrides passed inside a DrupalCI test definition (e.g. .drupalci.yml)
 * 5. Custom overrides located inside a test definition defined via the $source variable when calling this function.
 */

namespace DrupalCI\Plugin\BuildSteps\configure;

use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginBase;
use DrupalCI\Console\Helpers\ConfigHelper;
use DrupalCI\Plugin\PluginManager;
use Symfony\Component\Yaml\Yaml;

/**
 * @PluginID("compile_definition")
 */
class CompileDefinition extends PluginBase {

  /**
   * @var \DrupalCI\Plugin\PluginManager;
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data = NULL) {
    Output::writeLn("<info>Calculating job definition</info>");
    // Get and parse the default definition template (containing %DCI_*%
    // placeholders) into the job definition.

    // For 'generic' jobs, this is the file passed in on the
    // 'drupalci run <filename>' command; and should be fully populated (though
    // template placeholders *can* be supported).

    // For other 'jobtype' jobs, this is the file located at
    // DrupalCI/Plugin/JobTypes/<jobtype>/drupalci.yml.
    if (!$definition = $this->loadYaml($job->getDefinitionFile())) {
      $job->errorOutput('Error', 'Failed to load job definition YAML');
    }
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
    // Foreach DCI_* pair in the array, check if a definition plugin exists,
    // and process if it does.  We pass in the test definition template and
    // complete array of DCI_* variables.
    foreach ($dci_variables as $key => $value) {
      if (preg_match('/^DCI_(.+)$/', $key, $matches)) {
        $name = strtolower($matches[1]);
        $replacements["%$key%"] = $value;
        if ($plugin_manager->hasPlugin('definition', $name)) {
          $plugin_manager->getPlugin('definition', $name)
            ->process($definition, $value, $dci_variables);
        }
      }
    }

    // Add support for substituting '%HOME%' with the $HOME env variable
    $replacements["%HOME%"] = getenv("HOME");

    // Process DCI_* variable substitution into test definition template
    $search = array_keys($replacements);
    $replace = array_values($replacements);
    array_walk_recursive($definition, function (&$value) use ($search, $replace) {
      $value = str_ireplace($search, $replace, $value);
    });
    // Attach the complete set of build variables and processed job definition to the job object
    $job->setBuildVars($dci_variables + $job->getBuildVars());
    $job->setDefinition($definition);
    return;
  }

  /**
   * Given a file, returns an array containing the parsed YAML contents from that file
   *
   * @param $source
   *   A YAML source file
   * @return array
   *   an array containing the parsed YAML contents from the source file
   */
  protected function loadYaml($source) {
    if ($content = file_get_contents($source)) {
      return Yaml::parse($content);
    }
    return [];
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
}
