<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\ComposerInstall
 */

namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("composerinstall")
 *
 * PreProcesses DCI_ComposerInstall variables, updating the job definition with
 * a install:composer:install section.  To use set DCI_ComposerInstall=true.
 */

class ComposerInstall {

  /**
   * {@inheritdoc}
   */
  public function process(array &$definition, $value, $dci_variables) {
    // Presence of the DCI_ComposerInstall variable infers we want to run it.
    if ($value == FALSE) {
      return;
    }

    if (empty($definition['install'])) {
      $definition['install'] = [];
    }

    // Make sure our composer steps run before any other install steps
    $original_install = $definition['install'];
    $original_composer = (!empty($definition['install']['composer'])) ? $definition['install']['composer'] : [];
    unset($original_install['composer']);
    $new_install['composer'] = $original_composer;
    $new_install['composer'][] = 'install --working-dir /var/www/html/core --prefer-dist';
    $new_install += $original_install;

    $definition['install'] = $new_install;
  }
}
