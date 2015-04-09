FROM       drupalci/web-base
MAINTAINER drupalci

##
# PHP 5.4.31
##

RUN sudo php-build -i development --pear 5.4.39 $HOME/.phpenv/versions/5.4.39
RUN sudo chown -R root:root $HOME/.phpenv
RUN phpenv rehash
RUN phpenv global 5.4.39
RUN echo | pecl install mongo
RUN echo | pecl install apc
RUN cp -f /root/.phpenv/shims/php /usr/bin/php

# Fix date.timezone warning
RUN find /root/.phpenv/ -iname php.ini -exec sh -c 'echo "date.timezone=UTC" >> {}' \;
RUN echo "date.timezone=UTC" >> /etc/php5/cli/php.ini

# Disable xdebug
RUN find $HOME/ -name xdebug.ini -exec sed -i 's/^/;/g' {} \;

RUN find / -name php.ini -exec sed -i 's/^display_errors = On/;display_errors = On/g' {} \;
RUN find / -name php.ini -exec sed -i 's/error_reporting = E_ALL/error_reporting = E_ALL \& ~E_DEPRECATED/g' {} \;
RUN find / -name php.ini -exec sed -i 's/^html_errors = On/;html_errors = On/g' {} \;
RUN find / -name php.ini -exec sed -i 's/^max_execution_time = 30/max_execution_time = 300/g'  {} \;
RUN find / -name php.ini -exec sed -i 's/memory_limit = 128M/memory_limit = 320M/g' {} \;
RUN find / -name php.ini -exec sed -i 's/serialize_precision = 17/serialize_precision = 100/g' {} \;
RUN find / -name php.ini -exec sed -i 's/track_errors = On/track_errors = Off/g' {} \;
RUN find / -name php.ini -exec sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 8M/g' {} \;
RUN find / -name php.ini -exec sed -i 's/;session.save_path/session.save_path/g' {} \;

EXPOSE 80
CMD ["/bin/bash", "/start.sh"]
