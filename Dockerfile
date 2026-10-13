# PHP 8.5 CLI (Trixie) — local + CI tooling image for jooservices/dto
FROM php:8.5-cli-trixie

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        unzip \
        libicu-dev \
        libzip-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) intl \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* \
    && groupadd --gid 10001 dto \
    && useradd --uid 10001 --gid 10001 --create-home --shell /usr/sbin/nologin dto

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

WORKDIR /app
USER 10001:10001

CMD ["php", "-v"]
