<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\JunitXml
 *
 * PreProcesses DCI_JunitXml variable, and creates the requested
 * directory.  This directory is then used as the destination for the
 * Junit Xml Formatter plugin.
 */
namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("junitxml")
 */
class JunitXml {

  /**
   * {@inheritdoc}
   *
   * DCI_JunitXml_Preprocessor
   *
   * Takes a directory value, and adds a junit_xmlformat step to the job
   * definition with that value within the job definition.  That step will
   * then create the directory on the host and use it as the output destination
   * for re-formatted xml processed by the JunitXML Formatter plugin.
   */
  public function process(array &$definition, $value) {
    // TODO: Sanitize to ensure we're not traversing out of the working directory
    $definition['publish']['junit_xmlformat'] = $value;
  }
}

