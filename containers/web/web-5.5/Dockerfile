FROM       drupalci/web-base
MAINTAINER drupalci

##
# PHP 5.5.15
##

RUN sudo php-build -i development --pear 5.5.23 $HOME/.phpenv/versions/5.5.23
RUN sudo chown -R root:root $HOME/.phpenv
RUN phpenv rehash
RUN phpenv global 5.5.23
RUN echo | pecl install mongo
RUN echo | pecl install channel://pecl.php.net/APCu-4.0.7
RUN cp -f /root/.phpenv/shims/php /usr/bin/php

# Fix date.timezone warning
RUN find /root/.phpenv/ -iname php.ini -exec sh -c 'echo "date.timezone=UTC" >> {}' \;
RUN echo "date.timezone=UTC" >> /etc/php5/cli/php.ini

# Disable xdebug
RUN find $HOME/ -name xdebug.ini -exec sed -i 's/^/;/g' {} \;

EXPOSE 80
CMD ["/bin/bash", "/start.sh"]
