FROM drupalci/db-base
MAINTAINER drupalci

# Add MySQL APT repository.
# See https://dev.mysql.com/doc/mysql-apt-repo-quick-guide/en/

COPY ./conf/mysql.list /etc/apt/sources.list.d/mysql.list
RUN apt-key adv --keyserver pgp.mit.edu --recv-keys 5072E1F5 \
    && apt-get update \
    && apt-get -y install mysql-server netcat \
    && apt-get clean \
    && apt-get autoclean \
    && apt-get -y autoremove \
    && rm -rf /var/lib/mysql/*

USER root

COPY ./conf/startup.sh /opt/startup.sh

CMD ["/bin/bash", "/opt/startup.sh"]
