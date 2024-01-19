FROM zenika/alpine-chrome AS chrome

USER root
RUN apk add --no-cache openssh openrc sudo
RUN echo "chrome:Docker!" | chpasswd
# allow root login via ssh using password
RUN sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config
RUN ssh-keygen -A

COPY ./.docker/chrome/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

FROM sylius/standard:1.11-traditional AS sylius

FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive
ARG PHP_VERSION=8.0
ENV LC_ALL=C.UTF-8
ENV XDEBUG_MODE=off

RUN apt-get update
# 'software-properties-common' to get command add-apt-repository
RUN apt-get install -y supervisor curl unzip git make iputils-ping sudo vim software-properties-common openssh-client

RUN add-apt-repository ppa:ondrej/php \
    && add-apt-repository ppa:ondrej/nginx

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -

RUN apt-get update \
    && apt-get install -y nginx nodejs

RUN npm install -g yarn && npm cache clean --force

RUN apt-get install -y php${PHP_VERSION} php${PHP_VERSION}-apcu php${PHP_VERSION}-calendar php${PHP_VERSION}-common php${PHP_VERSION}-cli php${PHP_VERSION}-ctype php${PHP_VERSION}-curl php${PHP_VERSION}-dom php${PHP_VERSION}-exif php${PHP_VERSION}-fpm php${PHP_VERSION}-gd php${PHP_VERSION}-intl php${PHP_VERSION}-mbstring php${PHP_VERSION}-mysql php${PHP_VERSION}-opcache php${PHP_VERSION}-pdo php${PHP_VERSION}-pgsql php${PHP_VERSION}-sqlite php${PHP_VERSION}-xml php${PHP_VERSION}-xdebug php${PHP_VERSION}-xsl php${PHP_VERSION}-yaml php${PHP_VERSION}-zip

# XDebug crashes on PHP 8.0.0 during some web requests and during symfony server:start (which uses PHP-FPM) with exited on signal 11 (SIGSEGV - core dumped)
RUN phpdismod -s fpm xdebug
# opcache causes for symfony server:start error during certificate verification 'remote error: tls: unknown certificate authority' (even after restart)
RUN phpdismod opcache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename composer
RUN curl -sS https://get.symfony.com/cli/installer | bash -s -- --install-dir="$(pwd)" && mv "$(pwd)"/symfony /usr/local/bin/symfony
# do not install symfony certificate via 'symfony server:ca:install' here as it causes problems with certificate verification 'remote error: tls: unknown certificate authority'

RUN apt-get remove --purge -y software-properties-common \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=sylius /etc/supervisor/conf.d/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
#COPY --from=sylius /etc/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./.docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY --from=sylius /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
COPY --from=sylius /etc/php/${PHP_VERSION}/fpm/php.ini /etc/php/${PHP_VERSION}/fpm/php.ini
COPY --from=sylius /etc/php/${PHP_VERSION}/cli/php.ini /etc/php/${PHP_VERSION}/cli/php.ini

COPY ./.docker/nginx/nginx-selfsigned.crt /etc/nginx/ssl/nginx-selfsigned.crt
COPY ./.docker/nginx/nginx-selfsigned.key /etc/nginx/ssl/nginx-selfsigned.key
COPY ./.docker/etc/sudoers.d/* /etc/sudoers.d/

RUN ln -s /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm && mkdir -p /run/php

RUN usermod --home /home/www-data --shell /bin/bash www-data \
    && mkdir -p /home/www-data \
    && chown -R www-data:www-data /home/www-data
RUN groupadd symfony \
    && usermod --append --groups symfony www-data

COPY ./.docker /.docker
RUN chmod +x /.docker/*.sh

WORKDIR /app

EXPOSE 80/tcp 443/tcp

CMD bash -c 'bash /.docker/init-working-user.sh /app && /usr/bin/supervisord -c /etc/supervisor/supervisord.conf'
