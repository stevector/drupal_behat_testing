FROM       drupalci/web-base
MAINTAINER drupalci

##
# PHP 5.5.23
##

RUN sudo php-build -i development --pear 5.5.23 /opt/phpenv/versions/5.5.23 && \
    sudo chown -R root:root /opt/phpenv && \
    phpenv rehash && \
    phpenv global 5.5.23 && \
    ln -s /opt/phpenv/shims/php /usr/bin/php
RUN echo | pecl install mongo && \
    echo | pecl install channel://pecl.php.net/APCu-4.0.7

##
# copying ini for compiled php
##
COPY ./conf/cli-php.ini /etc/php5/cli/php.ini
COPY ./conf/opt-php.ini /opt/phpenv/versions/5.5.23/etc/php.ini
COPY ./conf/opt-apc.ini /opt/phpenv/versions/5.5.23/etc/conf.d/apc.ini
COPY ./conf/opt-gettext.ini /opt/phpenv/versions/5.5.23/etc/conf.d/gettext.ini
COPY ./conf/opt-xdebug.ini /opt/phpenv/versions/5.5.23/etc/conf.d/xdebug.ini

EXPOSE 80
CMD ["/bin/bash", "/start.sh"]
