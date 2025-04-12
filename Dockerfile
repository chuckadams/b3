FROM dunglas/frankenphp:1.4.4-php8.4.4-bookworm@sha256:462c2d0eb8dd4fae16b7396f9873e0d52aab5c62d1abd0f1303bcf05041eed4e AS base

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y bash gpg postgresql-client vim zip

RUN install-php-extensions mysqli pdo pdo_mysql opcache

FROM base AS dev

RUN install-php-extensions xdebug
