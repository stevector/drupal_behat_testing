FROM       drupalci/web-base
MAINTAINER drupalci

##
# Downgrade drush
##
# Drush and dependencies.
RUN apt-get clean && apt-get update
RUN apt-get install -y php5-cli
RUN HOME=/ /usr/bin/php /usr/local/bin/composer global require drush/drush:7.0.0
RUN /.composer/vendor/drush/drush/drush --version
RUN apt-get -y remove php5-cli

##
# PHP 5.3.29
##

RUN sudo php-build -i development --pear 5.3.29 /opt/phpenv/versions/5.3.29 && \
    sudo chown -R root:root /opt/phpenv && \
    phpenv rehash && \
    phpenv global 5.3.29 && \
    ln -s /opt/phpenv/shims/php /usr/bin/php

RUN echo | pecl install mongo && \
    echo | pecl install apc

##
# copying php.ini for compiled php
##
COPY ./conf/cli-php.ini /etc/php5/cli/php.ini
COPY ./conf/opt-php.ini /opt/phpenv/versions/5.3.29/etc/php.ini
COPY ./conf/opt-apc.ini /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini
COPY ./conf/opt-gettext.ini /opt/phpenv/versions/5.3.29/etc/conf.d/gettext.ini
COPY ./conf/opt-xdebug.ini /opt/phpenv/versions/5.3.29/etc/conf.d/xdebug.ini

## Fix date.timezone warning
#RUN find /opt/phpenv/ -iname php.ini -exec sh -c 'echo "date.timezone=UTC" >> {}' \;
#RUN echo "date.timezone=UTC" >> /etc/php5/cli/php.ini
#
## Disable xdebug
#RUN find /opt/phpenv/ -name xdebug.ini -exec sed -i 's/^/;/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/^display_errors = On/;display_errors = On/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/error_reporting = E_ALL/error_reporting = E_ALL \& ~E_DEPRECATED/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/^html_errors = On/;html_errors = On/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/^max_execution_time = 30/max_execution_time = 300/g'  {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/memory_limit = 128M/memory_limit = 320M/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/serialize_precision = 17/serialize_precision = 100/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/track_errors = On/track_errors = Off/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 8M/g' {} \; && \
#  find /opt/phpenv/ -name php.ini -exec sed -i 's/;session.save_path/session.save_path/g' {} \; && \
#  echo "apc.enabled = 1" > /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini && \
#  echo "apc.shm_size = 300M" >> /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini && \
#  echo "apc.enable_cli = 1" >> /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini && \
#  echo "apc.mmap_file_mask = /tmp/apc.XXXXXX" >> /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini && \
#  echo "apc.rfc1867 = 1" >> /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini && \
#  echo "apc.include_once_override = 0 " >> /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini && \
#  echo "extension=gettext.so" >> /opt/phpenv/versions/5.3.29/etc/conf.d/gettext.ini
EXPOSE 80
CMD ["/bin/bash", "/start.sh"]
