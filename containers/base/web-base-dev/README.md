containers/base/web-base
====

### Overview

This container is responsible for providing any packages needed by all
web containers in the drupalci container stack.

### Usage

No action is required to leverage this container if using the container images
as published on Docker Hub; as the pulled images will automatically contain the
base container layers by default.

If building the container images locally, this container depends on the
existence of the 'drupalci/php-base' container image before it can be built.

This container is a pre-requisite for each of the drupalci/web-<version>
containers.