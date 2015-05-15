#!/bin/bash
set -uex
echo "Creating directories for docker binds"
echo "adding tmpfs mount"
DCIPATH="/var/lib/drupalci"

if [ -d $DCIPATH ];
then
  DATE="$(date +'%Y%m%d%H%M')"
  mv $DCIPATH $DCIPATH-$DATE
fi

mkdir -p $DCIPATH

## TMPFS
sudo mount -t tmpfs -o size=1G tmpfs $DCIPATH

mkdir -p $DCIPATH/web
mkdir -p $DCIPATH/database/mariadb-5.5
mkdir -p $DCIPATH/database/mariadb-10.0
mkdir -p $DCIPATH/database/mysql-5.5
mkdir -p $DCIPATH/database/pgsql-9.1
mkdir -p $DCIPATH/database/pgsql-9.4

echo "Changing ownership for the directories"
# setting the uid:gid to www-data
chown -R 33:33 $DCIPATH/web
# setting the uid:gid to database (mysql/postgres)
chown -R 102:102 $DCIPATH/database

