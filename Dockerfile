FROM gmaiztegi/nginx-php-fpm as build

ARG APP_ENV=dev
ARG APP_DEBUG=1

RUN apk add -U \
    yarn \
    && rm -rf /var/cache/apk/*
COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY . /var/www/app

ENV APP_ENV ${APP_ENV}
ENV APP_DEBUG ${APP_DEBUG}

RUN composer install --no-dev \
    && yarn install \
    && yarn run encore $(if [ "x${APP_ENV}" = "xprod" ] ; then echo production; else echo ${APP_ENV};fi)

FROM gmaiztegi/nginx-php-fpm
MAINTAINER Gorka Maiztegi <gorkamaiztegi@gik.blue>

ARG APP_ENV=dev
ARG APP_DEBUG=1

ENV APP_ENV ${APP_ENV}
ENV APP_DEBUG ${APP_DEBUG}

RUN sed -i "s,root /var/www/app/\\;,root /var/www/app/public\\;," /etc/nginx/nginx.conf

COPY --chown=nginx:nginx . /var/www/app
COPY --from=build --chown=nginx:nginx /var/www/app/vendor /var/www/app/vendor
COPY --from=build --chown=nginx:nginx /var/www/app/public/build /var/www/app/public/build
COPY --from=build --chown=nginx:nginx /var/www/app/public/bundles /var/www/app/public/bundles

ENV APP_ENV ${APP_ENV}
ENV APP_DEBUG ${APP_DEBUG}

RUN bin/console cache:clear

EXPOSE 80
