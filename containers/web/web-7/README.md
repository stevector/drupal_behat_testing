containers/web/web-7
====

### Overview

This container is responsible for providing an Apache + PHP 7 executable
container environment for direct job execution.

### Usage

To obtain the web-7 image for use on your local environment, select the
7 container option during the web container selection step inside of the
'drupalci init' command.  To obtain the image without re-running drupalci init,
any of the following methods may be used:
- drupalci init:web (and select the 7 option)
  or
- drupalci pull drupalci/web-7
  or
- docker pull drupalci/web-7

If building the container images locally, this container depends on the
existence of the 'drupalci/web-base' container image before it can be built.
