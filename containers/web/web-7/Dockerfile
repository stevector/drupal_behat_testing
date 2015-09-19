FROM       drupalci/web-base
MAINTAINER drupalci

##
# PHP master (7)
##
RUN echo "--enable-intl" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
    echo "--enable-wddx" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
    /opt/phpenv/plugins/php-build/install.sh && \
    sudo php-build -i development --pear master /opt/phpenv/versions/master && \
    sudo chown -R root:root /opt/phpenv && \
    phpenv rehash && \
    phpenv global master && \
    ln -s /opt/phpenv/shims/php /usr/bin/php && \
    rm -rf /tmp/pear /tmp/php-build*

# TODO: pecl mongo not working yet
# RUN echo | pecl install mongo
# TODO: test apcu on php 7
# RUN echo | pecl install channel://pecl.php.net/APCu-4.0.7

## Upgrade curl to version 7.38
RUN apt-get update && \
    apt-get -y install software-properties-common && \
    add-apt-repository -y ppa:n-muench/programs-ppa2 && \
    apt-get update && \
    apt-get install -y curl && \
    apt-get clean && apt-get -y autoremove

##
# copying php.ini for compiled php
##
COPY ./conf/cli-php.ini /etc/php5/cli/php.ini
COPY ./conf/opt-php.ini /opt/phpenv/versions/master/etc/php.ini
COPY ./conf/opt-apcu.ini /opt/phpenv/versions/master/etc/conf.d/apcu.ini
COPY ./conf/opt-gettext.ini /opt/phpenv/versions/master/etc/conf.d/gettext.ini
#COPY ./conf/opt-xdebug.ini /opt/phpenv/versions/master/etc/conf.d/xdebug.ini

CMD ["/bin/bash", "/start.sh"]
