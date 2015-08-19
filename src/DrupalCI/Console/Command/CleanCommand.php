<?php

/**
 * @file
 * Command class for clean.
 */

namespace DrupalCI\Console\Command;

use DrupalCI\Console\Command\DrupalCICommandBase;
use DrupalCI\Console\Helpers\ContainerHelper;
use DrupalCI\Console\Output;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CleanCommand extends DrupalCICommandBase {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('clean')
      ->setDescription('Remove docker images and containers.')
      ->addArgument('type', InputArgument::REQUIRED, 'Type of container to clean.')
      ->addOption('hard', '', InputOption::VALUE_NONE, 'Remove everything, stopping first if neccessary.')
      ->addOption('list', '', InputOption::VALUE_NONE, 'List images or containers.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output) {
    $helper = new ContainerHelper();
    $containers = $helper->getAllContainers();
    $types = array(
      'images', 'containers', 'db', 'web', 'environment', 'all',
    );
    $type = $input->getArgument('type');
    if (!in_array($type, $types)) {
      $output->writeln('<error>' . $type . ' is not a legal container type.</error>');
    }
    //$this->showArguments($input, $output);
    if ($input->getOption('list')) {
      $this->listContainers($type, $input, $output);
    }
    else {
      // TODO: handle cleaning of just certain types, currently just cleans all
      $this->cleanContainers($type, $input, $output);
    }
  }

  /**
   * (@inheritdoc)
   */
  protected function listContainers($type, InputInterface $input, OutputInterface $output) {
    Output::setOutput($output);
    $helper = new ContainerHelper();
    $containers = $helper->getAllContainers();
    foreach ($containers as $containerLabel => $containerName) {
      Output::writeln("<comment>$containerLabel, $containerName</comment>");
    }
  }

  /**
   * (@inheritdoc)
   */
  protected function cleanContainers($type, InputInterface $input, OutputInterface $output) {
    // TODO: replace PHP exec('docker ...') with docker-php
    Output::setOutput($output);
    // get list of running containers
    exec('docker ps -q', $runningContainers);
    // get list of created containers
    exec('docker ps -a -q', $createdContainers);
    $result_code = 0;
    if ($createdContainers) {
      // stop running containers and clean up
      Output::writeln('<comment>Cleaning containers.</comment>');
      if ($runningContainers) {
        exec('docker stop $(docker ps -q) && docker rm $(docker ps -a -q)', $clean_output, $result_code);
      }
      else {
        // no containerss running, so just clean up
        exec('docker rm $(docker ps -a -q)', $clean_output, $result_code);
      }
      // DEBUG
      // Output::writeln($clean_output);
    }
    else {
      Output::writeln('<comment>No containers to be cleaned.</comment>');
    }

    exec('docker ps -a -q', $clean_check);
    if ($clean_check || $result_code) {
      Output::writeln('<error>Error:</error>');
      Output::writeln($clean_check);
      Output::writeln('<comment>Docker result code:</comment> '.$result_code);
    }
    else {
      Output::writeln('<comment>Clean complete.</comment>');
    }
  }
}
