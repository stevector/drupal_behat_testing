FROM drupalci/db-base:dev
MAINTAINER drupalci

# Packages.
RUN apt-get -qq -y install software-properties-common && \
    apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xcbcb082a1bb943db && \
    add-apt-repository 'deb http://ftp.osuosl.org/pub/mariadb/repo/10.0/ubuntu trusty main' && \
    apt-get -y update && \
    apt-get -y install mariadb-server netcat && \
    apt-get clean && apt-get autoclean && apt-get -y autoremove

RUN rm -rf /var/lib/mysql/*

USER root

COPY ./conf/startup.sh /opt/startup.sh
COPY ./conf/my.cnf /etc/mysql/my.cnf

CMD ["/bin/bash", "/opt/startup.sh"]
