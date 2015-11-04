
Date: 2015-05-20 - Wednesday - 9:36am PST - This readme was ported from a [google docs collaborative draft](https://docs.google.com/document/d/1SwH_y4kZyMa_8FlucGOaEdGzFBGp2wB3pAgoLsWEs8s/edit#).

# DrupalCI Testbot
---
#### Contents
`Ctrl-f` for these topic headings or use a markdown renderer to use them as anchor links.

- [Project Synopsis](#synopsis)
- [What are we doing here?](#what)
- [Quickstart - OSX](#quick-mac)
- [Quickstart - Linux](#quick-linux)
- [Debugging Extras](#debug-tips)
- [Contributing](#contributing)
  - [Containers](#contrib-containers)
  - [Test Runner](#contrib-testrunner)
  - [run_tests.sh](#contrib-runsh)
  - [API](#contrib-api)
  - [Results Site](#contrib-results)
  - [D.O integration](#contrib-do)
  - [Documentation](#contrib-docs)

---
<a name="synopsis"></a>
### Purpose
#### [DrupalCI Project](https://www.drupal.org/project/drupalci)

This project is intended as a collection and organization point for the 'Modernizing Testbot' project, an unofficial Drupal community initiative with the goal of improving the Drupal.org automated testing environment. This is being accomplished through the design and implementation of a new automated testing / job dispatch infrastructure to replace the existing **PIFT/PIFR** module combination currently being used; increasing both the flexibility and functionality of the current environment in an attempt to bring it up to speed with other modern continuous integration platforms and better meet the evolved testing needs of the Drupal community.

This project is the centralized place for issues to be filed or questions to be asked about the new Docker-based testbots and all of the infrastructure and projects related to them. Since there are a number of sub-projects involved, and it may not be clear to most people which project a particular component falls under, this project will serve as a one-stop shop for issues; and the initiative maintainers will triage the issues out from here as needed.

### Components
Planned sub-projects of this initiative include the following:

#### [Drupal.org Integration](https://www.drupal.org/project/project_issue_file_test)

This sub-project is responsible for integration between drupal.org and the 'Modernizing Testbot' infrastructure. This project will host the repository for any custom drupal code/modules meant for deployment on drupal.org (and specifically related to this initiative).

#### [DrupalCI: API](https://www.drupal.org/project/drupalci_api)

This sub-project provides an API layer between Drupal.org and the DrupalCI dispatcher, allowing the modular substitution of backend components without requiring code changes within the Drupal.org integration module.

#### [DrupalCI: Dispatcher](https://www.drupal.org/project/drupalci_jenkins)

This sub-project is responsible for the scripts and configuration files used on the jenkins master/slave servers which serve as the central job dispatcher for the environment. It is anticipated that this repository will contain xml files of the jenkins job definitions and related scripts for import on the Jenkins server.

#### [DrupalCI: Test Runner](https://www.drupal.org/project/drupalci_testbot)

This sub-project is responsible for the test runners being used on the infrastructure. It is anticipated that this project's repository will contain the container definitions, build and run scripts, include files, and other associated code (bash scripts, etc.).

#### [DrupalCI: Results](https://www.drupal.org/project/drupalci_results)

This sub-project is responsible for the long-term archival, storage, and exposure of build artifacts and job/test results; similar to the (non-dispatch related) functions performed by qa.d.o today. It is anticipated that this project could end up being a full drupal site on it's own accord, and the repository will include any custom code related to that build.

#### [DrupalCI: Puppet Scripts](https://www.drupal.org/project/drupalci_puppet)

This sub-project will host the puppet configuration scripts used to build out the various servers which make up the environment.

### Get involved!

We are always looking for more volunteers to help with the design and implementation of the new testing environment, and would be happy to have you on board. If you are interested in helping out, please file an issue in this queue indicating your interest and which (if any) portion of the initiative you would be most interested in getting involved on. We also suggest subscribing to the 'Drupal.org Testing Infrastructure' group on groups.drupal.org.


---

<a name="what"></a>
## What are we doing here?
These instructions will install a virtual machine running Linux that has all the testbot hotness set up and ready to go. What is the hotness? It’s the whole DrupalCI testing stack; the drupalci command line utility and all of the DrupalCI Docker containers (https://registry.hub.docker.com/repos/drupalci/).

Please note that if you are doing any work involving building the containers, it can take several hours. Pulling down containers from the hub or working from already built containers can be much quicker.

---

<a name="quick-mac"></a>
## Quickstart - OSX
### Prerequisites
* **[Git](http://git-scm.com/downloads):**  - Version control system
* **[Composer](https://getcomposer.org/download/):** - A dependency manager for PHP
* **[Vagrant](https://www.vagrantup.com/downloads.html):** - Create and configure lightweight, reproducible, and portable development environments.
* **[VirtualBox](https://www.virtualbox.org/wiki/Downloads):**
Provides virtual machines so you can run other operating systems (Linux) in your host operating system (OS X).

### Actions
Get a copy of the latest production testbot code:

  git clone --branch production http://git.drupal.org/project/drupalci_testbot.git

Create a testbot virtual machine. This takes 30 minutes or so the first time:

  cd drupalci_testbot
  vagrant up

When it's done you'll see:

  ==> testbot: Box started, run vagrant halt to stop.
  ==> testbot:
  ==> testbot: To access the box and run tests, do:
  ==> testbot: vagrant ssh
  ==> testbot: cd drupalci_testbot

The vagrant VM is configured to use 1 gb memory, which is appropriate for a computer with 4 gb of memory. Increasing the amount of memory allocated to the VM for your hardware is recommended (4gb on a 16gb machine is fine). You can do this by editing this line in the Vagrantfile:

  v.customize [ "modifyvm", :id, "--memory", "1024" ]

Then you’d need to restart the vagrant box with `vagrant halt` and `vagrant up` again:

  vagrant ssh

This opens up a prompt to the testbot virtual machine; commands from now on happen inside the VM.

You'll know you're there because your prompt changes to:

  vagrant@vagrant-ubuntu-trusty-64:~$

Now you need to initialize the Testbot and Docker Containers:

  ./drupalci init   (or ‘./drupalci init --no-interaction’ to accept defaults)

This will pull down all the needed Docker Containers and will take some time.

Midway through you'll be asked to select which database environment(s) and web environment(s) to support. Hit Enter for the defaults which are mysql-5.5 / PHP 5.4.

When it finishes you'll see:

  Created /home/vagrant/.drupalci/configs directory.
  Created default configuration sets.
  Created initial config set at /home/vagrant/.drupalci/config

You probably don’t want to attempt a complete run, so we can override the Testgroups for the simpletest job:

  ./drupalci config:set DCI_TESTGROUPS=action

Afterwards you should be able to run:

  ./drupalci run simpletest

If you want to build your own test definition file (look at .drupalci.yml.example as a starting point, documentation will follow), you can instead run:

  ./drupalci run <filename>.yml

^^ `.yml` extension is currently required.

#### How to access results?
Currently, the test runner does not forward on results … you will need to either have the results saved on the locally mapped volume, or connect into the container to view them.

Connecting into a container after a test run to inspect results or debug:
During the test run, you should see something like this:

  Executing on container instance 9e30f788

9e30f788 is the container id. Slot it into this docker command:

  docker exec -i -t <container id> /bin/bash

Now you can `cd /var/www/html/sites/default/files` to see the results.

Type `exit` to leave the container.

Now you’re back at the vagrant box.

***TODO:*** Create a drupalci connect command to wrap this.

### Starting over
Removing all docker containers that were created:

  docker stop $(docker ps -q)
  docker rm $(docker ps -a -q)

Removing all docker images that were built(This might not be so useful.):

  docker rmi $(docker images -q)

Building a docker image with local changes. (Makes the last command to remove all the docker images a bit more useful). A local change made to a base image requires to also build dependent images locally. In other words, making a change to php-base requires building php-base, web-base, and web-5.4 locally.

  docker build -t drupalci/web-5.4 ~/drupalci_testbot/containers/web/web-5.4

Updating a single docker image from docker hub (should be current within about 1 hour):

  docker pull drupalci/lweb-5.4 (or drupalci/mysql-5.5, etc)
  drupalci pull <image name> will also work.

---

<a name="quick-linux"></a>
## Quickstart - Linux
### Prerequisites
Install Docker https://docs.docker.com/installation
Composer/PHP (below)

### Actions
Get a copy of the latest production testbot code.

  git clone --branch production http://git.drupal.org/project/drupalci_testbot.git
  cd drupalci_testbot

Now you need to install composer and initialize the Testbot’s Docker Containers (takes time):

  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composerq
  composer install
  sudo ./drupalci init

You probably don’t want to attempt a complete run, so we can override the Testgroups for the simpletest job:

  sudo ./drupalci config:set DCI_TESTGROUPS=action

Afterwards you should be able to run:

  sudo ./drupalci run simpletest

If you want to build your own test definition file (look at .drupalci.yml.example as a starting point, documentation will follow), you can instead run:

  sudo ./drupalci run <filename>.yml

^^ `.yml` extension is currently required.

---

<a name="debug-tips"></a>
## Debugging Extras:
Hard docker clean up from all running containers and drupalci images:

  $ sudo docker ps | awk '{print $1}' | xargs sudo docker stop
  $ sudo docker images | egrep 'drupalci|<none>' | awk '{print $3}' | xargs sudo docker rmi -f

Build the minimal set of containers for testing:

  ~/drupalci_testbot$ for CONTAINER in base db-base php-base web-base mysql-5.5 web-5.4; do sudo ./drupalci build drupalci/${CONTAINER}; done

Force build of all local containers:

  ~/drupalci_testbot$ time for i in {1..3}; do for CONTAINER in $(find -name Dockerfile | awk -F"/" '{print $4}' | sort  > /tmp/dcitmp ; grep base /tmp/dcitmp; grep -v base /tmp/dcitmp); do sudo ./drupalci build drupalci/${CONTAINER}; done; done

---

<a name="contributing"></a>
## Contributing
### Containers
<a name="contrib-container"></a>
The containers can be found in the `/containers` subdirectory of the DrupalCI: Test Runner project. Contributors should use the production branch, which contains the latest stable code.

The Containers are published on [DockerHub](https://registry.hub.docker.com/u/drupalci). The Canonical source for the code is the [Drupal.org project](http://drupal.org/project/drupalci_testbot).  However, since docker hub is hardcoded to a BitBucket repository, it pulls from the [BitBucket mirror](https://bitbucket.org/drupalci-aws/drupalci_testbot).

Containers rebuild once a day in the following order:

> base -> php-base -> web-base -> web-*-*
base -> db-base -> dbname

This process takes a couple of hours. You can see where things are currently at with a given container by clicking its `Build Details` tab, e.g. [https://registry.hub.docker.com/u/drupalci/db-mysql-5.5/builds_history/171387/](https://registry.hub.docker.com/u/drupalci/db-mysql-5.5/builds_history/171387/)

If it says `Finished` you are good. If it says `Building` it's still in process.

<a name="contrib-testrunner"></a>
### Test Runner

> ... please contribute additional documentation ...

<a name="contrib-runsh"></a>
### run_tests.sh

> ... please contribute additional documentation ...

<a name="contrib-api"></a>
### API


> ... please contribute additional documentation ...

<a name="contrib-results"></a>
### Results Site


> ... please contribute additional documentation ...

<a name="contrib-do"></a>
### D.O integration

> ... please contribute additional documentation ...

<a name="contrib-docs"></a>
### Documentation

> ... please contribute additional documentation ...


