FROM       drupalci/web-base
MAINTAINER drupalci

##
# PHP 5.6.0
##

RUN sudo php-build -i development --pear 5.6.7 /opt/phpenv/versions/5.6.7
RUN sudo chown -R root:root /opt/phpenv
RUN phpenv rehash
RUN phpenv global 5.6.7
RUN echo | pecl install mongo
RUN echo | pecl install channel://pecl.php.net/APCu-4.0.7
RUN ln -s /opt/phpenv/shims/php /usr/bin/php

# Fix date.timezone warning
RUN find /opt/phpenv/ -iname php.ini -exec sh -c 'echo "date.timezone=UTC" >> {}' \;
RUN echo "date.timezone=UTC" >> /etc/php5/cli/php.ini

# Disable xdebug
RUN find /opt/phpenv/ -name xdebug.ini -exec sed -i 's/^/;/g' {} \;

EXPOSE 80
CMD ["/bin/bash", "/start.sh"]
