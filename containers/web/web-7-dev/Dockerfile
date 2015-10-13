FROM       drupalci/web-base:dev
MAINTAINER drupalci

##
# PHP 7.0snapshot
##
RUN echo "--enable-intl" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
    echo "--enable-wddx" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
    /opt/phpenv/plugins/php-build/install.sh && \
    sudo php-build -i development --pear 7.0snapshot /opt/phpenv/versions/7.0snapshot && \
    sudo chown -R root:root /opt/phpenv && \
    phpenv rehash && \
    phpenv global 7.0snapshot && \
    ln -s /opt/phpenv/shims/php /usr/bin/php && \
    rm -rf /tmp/pear /tmp/php-build*

# TODO: pecl mongo not working yet
# RUN echo | pecl install mongo
# TODO: test apcu on php 7
# RUN echo | pecl install channel://pecl.php.net/APCu-4.0.7
# TODO: no pecl yaml on php 7
# RUN pecl install yaml < /usr/lib/x86_64-linux-gnu

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
COPY ./conf/opt-php.ini /opt/phpenv/versions/7.0snapshot/etc/php.ini
COPY ./conf/opt-apcu.ini /opt/phpenv/versions/7.0snapshot/etc/conf.d/apcu.ini
COPY ./conf/opt-gettext.ini /opt/phpenv/versions/7.0snapshot/etc/conf.d/gettext.ini
#COPY ./conf/opt-xdebug.ini /opt/phpenv/versions/7.0snapshot/etc/conf.d/xdebug.ini

CMD ["/bin/bash", "/start.sh"]
