containers/base/db-base
====

### Overview

This container is responsible for providing any packages needed by all
database containers in the drupalci container stack.

### Details

Currently, there are no additional packages installed by the db-base image.
(i.e. db-base is a mirror of the "base" image.)  This image remains here as
an abstraction layer supporting the potential installation of any cross-db
packages which may be required in the future.

### Usage

No action is required to leverage this container if using the container images
as published on Docker Hub; as the pulled images will automatically contain the
base container layers by default.

If building the container images locally, this container depends on the
existence of the 'drupalci/base' container image before it can be built.

This container is a pre-requisite for each of the drupalci/<database> containers.