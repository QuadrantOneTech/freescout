####################################################################################################################
### Production layer with uvdesk.
####################################################################################################################
FROM php:7.4.11-fpm-alpine3.12

ARG GROUP_ID=505
ARG USER_ID=505

ENV PROJECTT_WORK_DIRRECTORY=/app
ENV TZ=America/New_York

COPY .docker/scripts/endless_stdout_tail.sh /usr/local/bin/endless_stdout_tail.sh
COPY --from=composer:2.2.23 /usr/bin/composer /usr/bin/composer
COPY .docker/etc/msmtprc /etc/msmtprc
COPY .docker/docker-php-entrypoint.sh /usr/local/bin/docker-php-entrypoint.sh
COPY .docker/docker-entrypoint.d /docker-entrypoint.d
COPY .docker/etc/crontabs/nonroot /etc/crontabs/nonroot

RUN set -eo && \
    export PHP_AUTOCONF=$(which autoconf) && \
    # Required for building PHP extensions
    apk add --no-cache \
        $PHPIZE_DEPS \
        imap-dev \
        krb5-dev \
        libxml2 \
        libxml2-dev \
        libzip \
        msmtp \
        zlib \
        zlib-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        linux-headers \
        openssl \
        openssl-dev \
        tzdata && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install zip xml imap mysqli pdo_mysql pdo gd && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    addgroup --gid $GROUP_ID nonroot && \
    adduser --uid $USER_ID --ingroup nonroot --shell /bin/sh --no-create-home --disabled-password nonroot && \
    mkdir -p /app /home/nonroot/.composer /custom-files && \
    chown -R nonroot: /home/nonroot /app /home/nonroot/.composer /custom-files && \
    chown nonroot:root /etc && \
    chmod +x /usr/local/bin/endless_stdout_tail.sh && \
    rm -rf /tmp/* && \
    chmod 0600 /etc/msmtprc && \
    chown nonroot: /etc/msmtprc && \
    chmod 0755 /usr/local/bin/docker-php-entrypoint.sh /docker-entrypoint.d/* && \
    chmod 0644 /etc/crontabs/nonroot && \
    wget https://github.com/aptible/supercronic/releases/download/v0.2.26/supercronic-linux-amd64 -O /usr/local/bin/supercronic && \
    chmod +x /usr/local/bin/supercronic

USER nonroot:nonroot

WORKDIR $PROJECTT_WORK_DIRRECTORY

COPY --chown=nonroot:nonroot . .

RUN COMPOSER_MEMORY_LIMIT=-1 composer install --prefer-dist --optimize-autoloader --no-interaction --no-dev && \
    rm -rf auth.json /tmp/* ~/.composer .docker && \
    php artisan storage:link && \
    touch .env

ENTRYPOINT [ "docker-php-entrypoint.sh" ]

STOPSIGNAL SIGQUIT

CMD [ "php-fpm", "-F", "-R" ]

EXPOSE 9000
