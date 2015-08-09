<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\DrupalInstall
 */

namespace DrupalCI\Plugin\Preprocess\definition;

/**
 * @PluginID("drupalinstall")
 *
 * PreProcesses DCI_DrupalInstall variables, updating the job definition with a install:[installmethod] section.
 */
class DrupalInstall {

  /**
   * {@inheritdoc}
   */
  public function process(array &$definition, $value, $dci_variables) {
    // Presence of the DCI_DrupalInstall variable infers that our core project
    // is Drupal Core.
    // TODO: Explicitly validate this.
    // How can we validate this?  Perhaps we make it a requirement to specify
    // environment:coreproject:drupal in the job definition?

    // Determine drupal core version.
    // TODO: Improve drupal core version detection.
    // We can get core version from DCI_CoreRepositoryBranch ... but that won't
    // work with feature branches. We should probably set DCI_DrupalCoreVersion
    // to this during variable pre-processing, so we can perform a simple and
    // explicit check here.
    if (!empty($dci_variables['DCI_DrupalCoreVersion'])) {
      $core_version = $dci_variables['DCI_DrupalCoreVersion'];
    }
    elseif (!empty($dci_variables['DCI_CoreBranch'])) {
      $core_version = $dci_variables['DCI_CoreBranch'];
    }
    else {
      // Assume Drupal Core 8.0.x by default
      // TODO: It would probably be better to throw an error and exit here.
      $core_version = '8.0.x';
    }

    $function = "process_{$value}_install";
    $this->$function($definition, $core_version, $dci_variables);
  }

  protected function process_composer_install(&$definition, $core_version, $dci_variables) {
    // Composer installs not supported on D7 core
    if (substr($core_version, 0, 1) == '7.') {
      Output::writeLn("<error>The 'Composer' installation method is not compatible with Drupal 7.</error>");
      // Currently, this will still attempt to run tests and then bail horribly.
      // TODO: Add more graceful error handling and failure.
      return;
    }
    // Add the 'composer' installation step to the job definition
    $definition['install']['composer'][] = 'install --working-dir core --prefer-dist';
  }

  protected function process_browser_install(&$definition, $core_version, $dci_variables) {
    // TODO: Implement browser click-through process

  }

  protected function process_drush_install(&$definition, $core_version, $dci_variables) {
    // TODO: Implement drush installation process
    if (empty($definition['install']['command'])) {
      $definition['install']['command'] = [];
    }
    $definition['install']['command'][] = "cd /var/www/html && /.composer/vendor/bin/drush si -y --db-url=%DCI_DBurl% --clean-url=0 --account-name=admin --account-pass=drupal --account-mail=admin@example.com";
    $definition['install']['command'][] = "cd /var/www/html && /.composer/vendor/bin/drush vset simpletest_clear_results '0' 2>&1";
    $definition['install']['command'][] = "cd /var/www/html && /.composer/vendor/bin/drush vset simpletest_verbose '0' 2>&1";
    $definition['install']['command'][] = "cd /var/www/html && /.composer/vendor/bin/drush en -y simpletest 2>&1";
  }
}
