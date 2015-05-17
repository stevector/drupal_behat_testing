containers/database/pgsql-9.1
====

### Overview

This container is responsible for providing a mysql 10.0 service container,
for jobs which require a pgsql-9.1 database environment.

### Usage

To obtain the pgsql-9.1 image for use on your local environment, select the
pgsql-9.1 container during the database selection step inside of the
'drupalci init' command.  To obtain the image without re-running drupalci init,
any of the following methods may be used:
- drupalci init:database (and select the pgsql-9.1 option)
  or
- drupalci pull drupalci/pgsql-9.1
  or
- docker pull drupalci/pgsql-9.1

If building the container images locally, this container depends on the
existence of the 'drupalci/db-base' container image before it can be built.
