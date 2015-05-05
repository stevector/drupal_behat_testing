FROM       drupalci/web-base
MAINTAINER drupalci

##
# PHP master (7)
##

RUN echo "--enable-intl" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
    echo "--enable-wddx" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options

RUN sudo php-build -i development --pear master /opt/phpenv/versions/master && \
    sudo chown -R root:root /opt/phpenv && \
    phpenv rehash && \
    phpenv global master && \
    ln -s /opt/phpenv/shims/php /usr/bin/php
# TODO: pecl mongo not working yet
# RUN echo | pecl install mongo
# TODO: test apcu on php 7
# RUN echo | pecl install channel://pecl.php.net/APCu-4.0.7

## Upgrade curl to version 7.38
RUN apt-get -y install software-properties-common && \
    add-apt-repository -y ppa:n-muench/programs-ppa2 && \
    apt-get update && \
    apt-get install -y curl
###
## copying php.ini for compiled php
###
#COPY ./conf/cli-php.ini /etc/php5/cli/php.ini
#COPY ./conf/opt-php.ini /opt/phpenv/versions/master/etc/php.ini
#COPY ./conf/opt-apcu.ini /opt/phpenv/versions/master/etc/conf.d/apcu.ini
#COPY ./conf/opt-gettext.ini /opt/phpenv/versions/master/etc/conf.d/gettext.ini
#COPY ./conf/opt-xdebug.ini /opt/phpenv/versions/master/etc/conf.d/xdebug.ini

# Fix date.timezone warning
RUN find /opt/phpenv/ -iname php.ini -exec sh -c 'echo "date.timezone=UTC" >> {}' \;
RUN echo "date.timezone=UTC" >> /etc/php5/cli/php.ini

# Disable xdebug
RUN find /opt/phpenv/ -name xdebug.ini -exec sed -i 's/^/;/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/^display_errors = On/;display_errors = On/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/error_reporting = E_ALL/error_reporting = E_ALL \& ~E_DEPRECATED/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/^html_errors = On/;html_errors = On/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/^max_execution_time = 30/max_execution_time = 300/g'  {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/memory_limit = 128M/memory_limit = 320M/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/serialize_precision = 17/serialize_precision = 100/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/track_errors = On/track_errors = Off/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 8M/g' {} \; && \
  find /opt/phpenv/ -name php.ini -exec sed -i 's/;session.save_path/session.save_path/g' {} \; && \
  echo "apc.enabled = 1" > /opt/phpenv/versions/master/etc/conf.d/apcu.ini && \
  echo "apc.shm_size = 300M" >> /opt/phpenv/versions/master/etc/conf.d/apcu.ini && \
  echo "apc.enable_cli = 1" >> /opt/phpenv/versions/master/etc/conf.d/apcu.ini && \
  echo "apc.mmap_file_mask = /tmp/apcu.XXXXXX" >> /opt/phpenv/versions/master/etc/conf.d/apcu.ini && \
  echo "apc.rfc1867 = 1" >> /opt/phpenv/versions/master/etc/conf.d/apcu.ini && \
  echo "apc.include_once_override = 0 " >> /opt/phpenv/versions/master/etc/conf.d/apcu.ini && \
  echo "extension=gettext.so" >> /opt/phpenv/versions/master/etc/conf.d/gettext.ini
EXPOSE 80
CMD ["/bin/bash", "/start.sh"]
