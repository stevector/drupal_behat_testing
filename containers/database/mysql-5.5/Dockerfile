FROM drupalci/db-base
MAINTAINER drupalci

COPY ./conf/my.cnf /etc/my.cnf
# Packages.
RUN apt-get -y install mysql-server netcat

RUN apt-get clean && apt-get autoclean && apt-get -y autoremove

RUN rm -rf /var/lib/mysql/*

USER root
EXPOSE 3306

COPY ./conf/startup.sh /opt/startup.sh

CMD ["/bin/bash", "/opt/startup.sh"]
