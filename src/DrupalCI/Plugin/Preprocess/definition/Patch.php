<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\Patch
 *
 * PreProcesses DCI_Patch variables, updating the job definition with a
 * setup:patch: section.
 */

namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("patch")
 */
class Patch {

  /**
   * {@inheritdoc}
   *
   * DCI_Patch_Preprocessor
   *
   * Takes a string defining patches to be applied, and converts this to a
   * 'setup:patch:' array as expected to appear in a job definition
   *
   * Input format: (string) $value = "file1.patch,patch_directory1;[file2.patch,patch_directory2];..."
   * Desired Result: [
   *   array('patch_file' => 'file1.patch', 'patch_directory' => 'patch_directory1')
   *   array('patch_file' => 'file2.patch', 'patch_directory' => 'patch_directory2')
   *       ...   ]
   */
  public function process(array &$definition, $value, $dci_variables) {
    // Stash the patch definition so that we can make sure it happens after the fetch.
    // TODO: unhack.
    if (!empty($definition['setup']['composer'])) {
      $composer_step = $definition['setup']['composer'];
      unset($definition['setup']['composer']);
    }
    if (empty($definition['setup']['patch'])) {
      $definition['setup']['patch'] = [];
    }
    foreach (explode(';', $value) as $patch_string) {
      if (strpos($patch_string, ',') === FALSE) {
        list($patch['patch_file'], $patch['patch_dir']) = array($patch_string, '.');
      }
      else {
        list($patch['patch_file'], $patch['patch_dir']) = explode(',', $patch_string);
      }
      $definition['setup']['patch'][] = $patch;
    }
    if (!empty($composer_step)){
          $definition['setup']['composer'] = $composer_step;
    }
  }
}
