FROM       drupalci/php-base:dev
MAINTAINER drupalci

##
# Base.
##

ENV DEBIAN_FRONTEND noninteractive
ENV HOME /root
ENV DRUPALCI TRUE

# Saves us from stale repository issues.
RUN apt-get clean && apt-get update && \
   apt-get install -y apache2 apache2-mpm-prefork apache2-dev && \
   apt-get -y remove php5-cli && \
   apt-get clean && apt-get autoremove -y

##
# PHPENV.
##

# Remove fpm since apxs2 (apache) support is being compiled.
RUN sed -i '/--enable-fpm/d' /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
    echo "--with-apxs2=/usr/bin/apxs2" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
    /opt/phpenv/plugins/php-build/install.sh

COPY ./conf/apache2/vhost.conf /etc/apache2/sites-available/drupal.conf
COPY ./conf/apache2/php5.conf /etc/apache2/mods-enabled/php5.conf

##
# Apache Configuration.
##
RUN a2dismod mpm_event && \
    a2enmod mpm_prefork && \
    a2enmod rewrite && \
    a2dissite 000-default.conf && \
    a2ensite drupal
