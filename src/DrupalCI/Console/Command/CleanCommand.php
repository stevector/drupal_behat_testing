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
      ->setDescription('Remove docker images and containers associated with DrupalCI.')
      ->addArgument('type', InputArgument::REQUIRED, 'Type of container to clean (e.g. db, web, all, etc.)')
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
  protected function imageTypes($type, InputInterface $input, OutputInterface $output) {
    // get list DCI container type
    $image_type = '';
    switch ($type) {
    case 'all':
      break;
    case 'db':
      $image_type = 'mysql|pgsql|mariadb|mongodb';
      break;
    case 'web':
      $image_type = 'web';
      break;
    case 'php':
      $image_type = 'php';
      break;
    }
    return $image_type;
  }

  /**
   * (@inheritdoc)
   */
  protected function cleanContainers($type, InputInterface $input,OutputInterface $output) {
    // TODO: replace PHP exec('docker ...') with docker-php
    Output::setOutput($output);

    // DCI search string
    $search_string = 'drupalci';
    $image_type = $this->imageTypes($type, $input, $output);
    if(!empty($image_type)){
      $search_string .= "' | egrep '" . $image_type;
    }

    // get list of created containers
    $cmd_docker_psa = "docker ps -a | grep '" . $search_string . "' | awk '{print $1}'";
    $cmd_docker_ps = "docker ps | grep '" . $search_string . "' | awk '{print $1}'";
    exec($cmd_docker_psa, $createdContainers);

    $result_code = 0;
    if($createdContainers) {
      Output::writeln('<comment>Cleaning containers.</comment>');
      // get list of running containers of desired type
      exec($cmd_docker_ps, $runningContainers);
      if(!empty($runningContainers)){
      // kill DCI running containers
        $cmd_docker_kill = "docker kill " . implode(' ', $runningContainers);
        exec( $cmd_docker_kill, $killContainers);
      }
      // remove DCI containers
      $cmd_docker_rm = "docker rm " . implode(' ', $createdContainers);
      exec( $cmd_docker_rm, $rmContainers);
      // DEBUG
      //Output::writeln($clean_output);

      exec($cmd_docker_psa, $clean_check);

      if ($clean_check || $result_code) {
        Output::writeln('<error>Error:</error>'); Output::writeln($clean_check);
        Output::writeln('<comment>Docker result code:</comment> '.$result_code);
      }
      else {
        Output::writeln('<comment>Clean complete.</comment>');
      }
    }
    else {
      // nothing to clean
      Output::writeln('<comment>Nothing to clean.</comment> ');
    }

  }
}
