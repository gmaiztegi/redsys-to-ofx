# Build JS and CSS assets
FROM node:10-alpine as node

ARG APP_ENV=prod
ENV APP_ENV=${APP_ENV}

RUN apk add --no-cache yarn python2

WORKDIR /var/www/app
COPY . /var/www/app

RUN yarn install \
    && yarn run encore $(if [ "x${APP_ENV}" = "xprod" ] ; then echo production; else echo ${APP_ENV};fi)

# Download PHP dependencies and build cache
FROM gmaiztegi/php:7.2-fpm-alpine as build

ARG APP_ENV=prod
ARG APP_DEBUG=0

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY . /var/www/app

WORKDIR /var/www/app

ENV APP_ENV ${APP_ENV}
ENV APP_DEBUG ${APP_DEBUG}

RUN composer install --no-dev \
    && bin/console cache:warmup

# Lean final release image
FROM gmaiztegi/php:7.2-fpm-alpine
MAINTAINER Gorka Maiztegi <gorkamaiztegi@gik.blue>

ARG APP_ENV=prod
ARG APP_DEBUG=0
ENV APP_ENV ${APP_ENV}
ENV APP_DEBUG ${APP_DEBUG}

WORKDIR /var/www/app

COPY . /var/www/app
COPY --from=build /var/www/app/vendor /var/www/app/vendor
COPY --from=build --chown=www-data:www-data /var/www/app/var /var/www/app/var
COPY --from=node /var/www/app/public/build /var/www/app/public/build
COPY --from=build /var/www/app/public/bundles /var/www/app/public/bundles

VOLUME "/var/www/app"
