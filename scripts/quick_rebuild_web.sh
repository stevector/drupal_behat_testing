#!/bin/bash -e

#
# This script is meant to help on rebuilding
# php-base, web-base and web-5.4
# start mysql-5.5
# for quick testing container changes
#

############### SETUP DEFAULT ENV VARIABLES ###############
# Remove intermediate containers after a successful build. Default is True.
DCI_REMOVEINTCONTAINERS=${DCI_REMOVEINTCONTAINERS:-"true"}
DCI_REPODIR=${DCI_REPODIR:-"$HOME/testbotdata"}
DCI_DBVER=${DCI_DBVER:-"5.5"}
DCI_DBTYPE=${DCI_DBTYPE:-"mysql"}
DCI_DRUPALBRANCH=${DCI_DRUPALBRANCH:-"8.0.x"}
DCI_PHPVERSION=${DCI_PHPVERSION:-"5.4"}
BASEDIR="$(pwd)"
BASEIFS="${IFS}"
###########################################################

cd containers/base/php-base
sudo ./build.sh
cd "${BASEDIR}"
cd containers/base/web-base
sudo ./build.sh
cd "${BASEDIR}"
cd containers/web/web-5.4
sudo ./build.sh
cd "${BASEDIR}"
cd containers/database/mysql-5.5
sudo ./run-server.sh
cd "${BASEDIR}"

echo -e "Run:"
echo -e "sudo DCI_CONCURRENCY="8" DCI_TESTGROUPS='--all' ./containers/web/run.sh"

