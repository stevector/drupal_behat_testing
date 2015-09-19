#!/bin/bash -e
#
# Name:         provision.sh
#
# Purpose:      quick start the vagrant box with all the things
#
# Comments:
#
# Usage:        vagrant up (on the repo root)
#
# Author:       Ricardo Amaro (mail_at_ricardoamaro.com)
# Contributors: Jeremy Thorson jthorson
# Contributors: Rudy Grigar (basic)
#
# Bugs/Issues:  Use the issue queue on drupal.org
#               IRC #drupal-testing
#
# Docs:         README.md for complete information
#

export HOME="/home/vagrant"

if [ -f /home/vagrant/drupalci_testbot/PROVISIONED ];
then
  echo "--------------------------------------------------------------------"
  echo
  echo "######                                      #####  ###"
  echo "#     # #####  #    # #####    ##   #      #     #  # "
  echo "#     # #    # #    # #    #  #  #  #      #        # "
  echo "#     # #    # #    # #    # #    # #      #        # "
  echo "#     # #####  #    # #####  ###### #      #        # "
  echo "#     # #   #  #    # #      #    # #      #     #  # "
  echo "######  #    #  ####  #      #    # ######  #####  ###   TESTBOT"
  echo ""
  echo "--------------------------------------------------------------------"
  echo
  echo "Hi there, it is your local Testbot!"
  echo
  echo "You seem to have this box already installed - which is a good thing!"
  echo "Documentation can be found in README.md or read on..."
  echo ""
else
  echo 'cd /home/vagrant/drupalci_testbot' >> /home/vagrant/.bashrc
  echo 'Defaults        env_keep +="HOME"' >> /etc/sudoers
  echo "Installing and building the all the things..."
  echo "on: $(hostname) with user: $(whoami) home: $HOME"
  swapoff -a
  dd if=/dev/zero of=/var/swapfile bs=1M count=2048
  chmod 600 /var/swapfile
  mkswap /var/swapfile
  swapon /var/swapfile
  /bin/echo "/var/swapfile swap swap defaults 0 0" >>/etc/fstab
  apt-get update && apt-get upgrade -y
  apt-get install -y git mc ssh gawk grep sudo htop mysql-client php5-cli curl php5-curl php5-mysql php5-pgsql php5-sqlite postgresql-client postgresql-client-common sqlite3
  apt-get autoclean && apt-get autoremove -y

  echo "Installing docker"
  curl -sSL get.docker.io | sh 2>&1 | egrep -i -v "Ctrl|docker installed"
  usermod -a -G docker vagrant
  cd /home/vagrant/drupalci_testbot

  echo "Installing composer"
  curl -sS https://getcomposer.org/installer | php
  echo "Running php composer.phar update"
  php composer.phar update

  echo "Creating drupalci symlink"
  ln -s /home/vagrant/drupalci_testbot /opt/drupalci_testbot
  ln -s /home/vagrant/drupalci_testbot/drupalci /usr/local/bin/drupalci

  echo "Creating directories for docker binds"
  DCIPATH="/var/lib/drupalci"
  mkdir -p $DCIPATH
  mkdir -p $DCIPATH/web
  mkdir -p $DCIPATH/database

  echo "Changing ownership for the directories"
  # setting the uid:gid to www-data
  chown  vagrant:vagrant $DCIPATH
  chown -R vagrant:www-data $DCIPATH/web
  # setting the uid:gid to database (mysql/postgres)
  chown -R vagrant:102 $DCIPATH/database
  adduser vagrant www-data
  chmod -R 775 $DCIPATH

  #Update/change cli php.ini
  echo "Updating php.ini for cli"
  sed -i 's/; sys_temp_dir = "\/tmp"/sys_temp_dir = "\/var\/lib\/drupalci\/web\/"/g' /etc/php5/cli/php.ini
  sed -i 's/variables_order = \"GPCS\"/variables_order = \"EGPCS\"/g' /etc/php5/cli/php.ini

  touch PROVISIONED

fi

chown -fR vagrant:vagrant /home/vagrant
echo "Box started up, run *vagrant halt* to stop."
echo
echo "To access the box and run tests, run:"
echo "- vagrant ssh"

