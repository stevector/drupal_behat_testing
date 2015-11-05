containers/base/base
====

### Overview

This container is responsible for providing any base packages needed by all
other containers in the drupalci container stack.

### Details

Currently, the base container installs the following packages, on top
of an ubuntu:trusty base:
- vim-tiny
- wget

### Usage

No action is required to leverage this container if using the container images
as published on Docker Hub; as the pulled images will automatically contain the
base container layers by default.

If building the container images locally, this container is a pre-requisite for
the building of the drupalci/db-base and/or drupalci/php-base containers.