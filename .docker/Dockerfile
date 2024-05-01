FROM my-proxy/library/php:8.0.14-fpm as base

ARG ENV

RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    supervisor \
    netcat \
    nginx \
    libmagickwand-dev

# configure and install php extentions
RUN docker-php-ext-install pdo_mysql zip sockets pcntl exif gd && \
    pecl install imagick && docker-php-ext-enable imagick

RUN if [ "$ENV" = "development" ] || [ "$ENV" = "local" ]; then pecl install xdebug; fi

# add user for php-fpm / nginx processes
RUN useradd -d /home/gas -s /bin/bash gas && \
    mkdir /home/gas && \
    mkdir /home/gas/.ssh && \
    mkdir /home/gas/application && \
    chown -R gas:gas /run && \
    chown -R gas:gas /home/gas && \
    chown -R gas:gas /var/log/nginx/ && \
    chown -R gas:gas /var/lib/nginx/ && \
    usermod -aG adm gas

RUN if [ "$ENV" = "development" ] || [ "$ENV" = "local" ]; then chown -R gas:gas /usr/local/etc/php/conf.d; fi

# get composer to resolve dependencies
RUN curl --silent --show-error https://getcomposer.org/installer | php -- --version=1.10.16 && \
    mv composer.phar /usr/local/bin/composer

COPY --chown=gas:gas .docker/nginx.conf /etc/nginx/nginx.conf
COPY --chown=gas:gas .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --chown=gas:gas .docker/php.ini /usr/local/etc/php/php.ini
COPY --chown=gas:gas .docker/php-xdebug.ini /usr/local/etc/php/disabled/php-xdebug.ini
COPY --chown=gas:gas ./.docker/auth.json /home/gas/.composer/auth.json

# run commands as gas user
USER gas

WORKDIR /home/gas/application

EXPOSE 8080

CMD ["/home/gas/application/.docker/entrypoint.sh"]

FROM base as deployment

ARG ENV

COPY --chown=gas:gas ./ /home/gas/application

USER gas
RUN if [ "$ENV" = "development" ]; then php -d memory_limit=2G /usr/local/bin/composer install; fi
RUN if [ "$ENV" = "production" ] || [ "$ENV" = "test" ] || [ "$ENV" = "stage" ]; then php -d memory_limit=2G /usr/local/bin/composer install --no-dev; fi

