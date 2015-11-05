FROM       drupalci/web-base:dev
MAINTAINER drupalci

##
# Downgrade drush
##
RUN apt-get clean && apt-get update && \
  apt-get install -y php5-cli && \
  HOME=/ /usr/bin/php /usr/local/bin/composer global require drush/drush:7.0.0 && \
  /.composer/vendor/drush/drush/drush --version && \
  apt-get -y remove php5-cli && \
  apt-get clean && apt-get -y autoremove

RUN mkdir /usr/include/freetype2/freetype && \
    ln -s /usr/include/freetype2/freetype.h /usr/include/freetype2/freetype/freetype.h

##
# PHP 5.3.29
##
RUN sudo php-build -i development --pear 5.3.29 /opt/phpenv/versions/5.3.29 && \
    sudo chown -R root:root /opt/phpenv && \
    phpenv rehash && \
    phpenv global 5.3.29 && \
    ln -s /opt/phpenv/shims/php /usr/bin/php && \
    rm -rf /tmp/pear /tmp/php-build*

RUN echo | pecl install apc

##
# Upgrade curl to version 7.38
##
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
COPY ./conf/opt-php.ini /opt/phpenv/versions/5.3.29/etc/php.ini
COPY ./conf/opt-apc.ini /opt/phpenv/versions/5.3.29/etc/conf.d/apc.ini
COPY ./conf/opt-gettext.ini /opt/phpenv/versions/5.3.29/etc/conf.d/gettext.ini

CMD ["/bin/bash", "/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
