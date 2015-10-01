containers/database/mongodb-2.6
====

### Overview

This container is responsible for providing a mysql 10.0 service container,
for jobs which require a mongodb-2.6 database environment.

### Usage

To obtain the mongodb-2.6 image for use on your local environment, select the
mongodb-2.6 container during the database selection step inside of the
'drupalci init' command.  To obtain the image without re-running drupalci init,
any of the following methods may be used:
- drupalci init:database (and select the mongodb-2.6 option)
  or
- drupalci pull drupalci/mongodb-2.6
  or
- docker pull drupalci/mongodb-2.6

If building the container images locally, this container depends on the
existence of the 'drupalci/db-base' container image before it can be built.
