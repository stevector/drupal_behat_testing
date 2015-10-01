containers/database/mysql-5.5
====

### Overview

This container is responsible for providing a mysql 10.0 service container,
for jobs which require a mysql-5.5 database environment.

### Usage

To obtain the mysql-5.5 image for use on your local environment, select the
mysql-5.5 container during the database selection step inside of the
'drupalci init' command.  To obtain the image without re-running drupalci init,
any of the following methods may be used:
- drupalci init:database (and select the mysql-5.5 option)
  or
- drupalci pull drupalci/mysql-5.5
  or
- docker pull drupalci/mysql-5.5

If building the container images locally, this container depends on the
existence of the 'drupalci/db-base' container image before it can be built.
