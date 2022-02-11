ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-fpm-alpine

RUN apk add --update && \
    apk add --no-cache wget \
    bash-completion \
    acl \
    curl \
    coreutils \
    gettext \
    less \
    vim \
    openssl \
    wget \
    nano \
    libzip-dev \
    zip \
    pax-utils \
    sudo \
    unzip \
    make \
    icu-dev \
    ca-certificates

RUN docker-php-ext-configure intl

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install intl
RUN docker-php-ext-install iconv
RUN docker-php-ext-install zip
RUN docker-php-ext-enable intl

RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version \
    && chmod 755 /usr/local/bin/composer
    # to enable xdebug, uncomment this
# RUN (pecl install xdebug || pecl install xdebug-2.5.5) && docker-php-ext-enable xdebug

RUN docker-php-ext-install -j$(nproc) mbstring ; \
    \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )";

# Conf PHP
RUN { \
		echo 'realpath_cache_size=4096k'; \
		echo 'realpath_cache_ttl=7200'; \
    } > /usr/local/etc/php/conf.d/php.ini

#COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
#RUN chmod +x /usr/local/bin/docker-entrypoint
#
#ENTRYPOINT ["docker-entrypoint"]

RUN rm -rf /var/cache/apk/*

COPY docker/php/bootstrap.sh /run/bootstrap.sh
RUN chmod 755 /run/bootstrap.sh

CMD ["/run/bootstrap.sh"]