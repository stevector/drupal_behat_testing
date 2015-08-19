<?php

/**
 * @file
 * Contains \DrupalCI\Job\Definition\JobDefinition.
 */

// TODO: This class does not appear to ever be called

namespace DrupalCI\Job\Definition;

use DrupalCI\Console\Output;
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
  public function setDefinition($job_definition) {  $this->definition = $job_definition;  }

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

    // Attempt to parse the job definition template.
    // The YAML class will throw an exception if this fails.
    $definition = $this->loadYaml($template_file);

    // Store the parsed template on this object
    $this->setDefinition($definition);
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
    throw new ParseException("Unable to parse empty job definition template file at $source.");
  }

  // Other potential methods for this class:
  // insert build step before/after
  // get/set DCI_parameters

}