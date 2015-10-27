FROM       drupalci/base
MAINTAINER drupalci

##
# Base
##

ENV DEBIAN_FRONTEND noninteractive
ENV HOME /root
ENV DRUPALCI TRUE

# Saves us from stale repository issues.
# Build packages.
# Make the PHP compiles go faster.
# re2c and bison are needed for compiling php7
# apache2-dev brings apxs2 into the game which is neede to compile php
RUN apt-get clean && apt-get update && \
    apt-get install -y \
    build-essential \
    bison \
    ccache \
    curl \
    freetds-dev \
    git \
    libaspell-dev \
    libbz2-dev \
    libc-client-dev \
    libcurl3-dev \
    libcurl4-openssl-dev \
    libdb5.1-dev \
    libfreetype6-dev \
    libgmp3-dev \
    libicu-dev \
    libjpeg-dev \
    libldap2-dev \
    libmcrypt-dev \
    libmhash-dev \
    libmysqlclient-dev \
    libmysqlclient15-dev \
    libpcre3-dev \
    libpng-dev \
    libpq-dev \
    libreadline6-dev \
    librecode-dev \
    libsnmp-dev \
    libsqlite-dev \
    libt1-dev \
    libtidy-dev \
    libxml2-dev \
    libssl-dev \
    libxpm-dev \
    libxslt-dev \
    libz-dev \
    make \
    mysql-client \
    ncurses-dev \
    php5-dev \
    postgresql-client \
    postgresql-client-common \
    re2c \
    software-properties-common \
    sqlite3 \
    sudo \
    supervisor \
    unixODBC-dev \
    unzip && \
   apt-get clean && apt-get autoremove -y && \
    rm /etc/cron.d/php5

##
# PHPENV.
##

RUN git clone --depth 1 https://github.com/CHH/phpenv.git /tmp/phpenv && \
    PHPENV_ROOT="/opt/phpenv" /tmp/phpenv/bin/phpenv-install.sh && \
    scp /tmp/phpenv/extensions/* /opt/phpenv/libexec/ && \
    echo 'eval "$(phpenv init -)"' >> /root/.bashrc && \
    git clone --depth 1 https://github.com/CHH/php-build.git /opt/phpenv/plugins/php-build
ENV PATH /opt/phpenv/shims:/opt/phpenv/bin:$PATH


# Small hack for running the php compilation with more than one cpu core
#RUN mv /usr/bin/make /usr/bin/make-system
#RUN echo "/usr/bin/make-system -j8 -l8" > /usr/bin/make
#RUN chmod +x /usr/bin/make

##
# Adding with-gettext flag
##
RUN echo "--with-gettext=shared" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
   echo "--with-freetype-dir=/usr/lib/x86_64-linux-gnu" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
   echo "--with-pdo-pgsql" >> /opt/phpenv/plugins/php-build/share/php-build/default_configure_options && \
   /opt/phpenv/plugins/php-build/install.sh

##
# Composer.
##

RUN bash -c "wget http://getcomposer.org/composer.phar && chmod 775 composer.phar && sudo mv composer.phar /usr/local/bin/composer"

# Drush and dependencies.
RUN HOME=/ /usr/local/bin/composer global require drush/drush:dev-master && \
    /.composer/vendor/drush/drush/drush --version

# supervisor
COPY ./conf/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Scripts.
COPY ./conf/scripts/start.sh /start.sh
COPY ./conf/mongodb.settings.php /mongodb.settings.php
COPY ./conf/scripts/foreground.sh /etc/apache2/foreground.sh
COPY daemon.sh /daemon.sh

# Make start.sh executable.
RUN chmod 755 /start.sh /daemon.sh
