FROM gmaiztegi/nginx-php-fpm
MAINTAINER Gorka Maiztegi <gorkamaiztegi@gik.blue>

RUN sed -i "s,root /var/www/app/\\;,root /var/www/app/public\\;," /etc/nginx/nginx.conf

ARG APP_ENV=dev
ARG APP_DEBUG=1

ENV APP_ENV ${APP_ENV}
ENV APP_DEBUG ${APP_DEBUG}

COPY --chown=nginx:nginx . /var/www/app

RUN bin/console cache:clear

EXPOSE 80
