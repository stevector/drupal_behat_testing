<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\Fetch
 *
 * PreProcesses DCI_Fetch variables, updating the job definition with a setup:fetch: section.
 */

namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("fetch")
 */
class Fetch {

  /**
   * {@inheritdoc}
   *
   * DCI_Fetch_Preprocessor
   *
   * Takes a string defining files to be fetched, and converts this to a
   * 'setup:fetch:' array as expected to appear in a job definition
   *
   * Input format: (string) $value = "http://example.com/file1.patch,destination_directory1;[http://example.com/file2.patch,destination_directory2];..."
   * Desired Result: [
   * array('url' => 'http://example.com/file1.patch', 'fetch_directory' => 'fetch_directory1')
   * array('url' => 'http://example.com/file2.patch', 'fetch_directory' => 'fetch_directory2')
   *      ...   ]
   */
  public function process(array &$definition, $value, $dci_variables) {
    // Stash the patch definition so that we can make sure it happens after the fetch.
    // TODO: unhack.
    if (!empty($definition['setup']['patch'])) {
      $patch_step = $definition['setup']['patch'];
      unset($definition['setup']['patch']);
    }
    if (!empty($definition['setup']['composer'])) {
      $composer_step = $definition['setup']['composer'];
      unset($definition['setup']['composer']);
    }
    if (empty($definition['setup']['fetch'])) {
      $definition['setup']['fetch'] = [];
    }
    foreach (explode(';', $value) as $fetch_string) {
      if (strpos($fetch_string, ',') === FALSE) {
        list($fetch['url'], $fetch['fetch_directory']) = array($fetch_string, '.');
      }
      else {
        list($fetch['url'], $fetch['fetch_directory']) = explode(',', $fetch_string);
      }
      $definition['setup']['fetch'][] = $fetch;
    }
    if (!empty($patch_step)){
      $definition['setup']['patch'] = $patch_step;
    }
    if (!empty($composer_step)){
          $definition['setup']['composer'] = $composer_step;
    }
  }
}
