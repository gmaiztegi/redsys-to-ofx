FROM gmaiztegi/nginx-php-fpm:latest
MAINTAINER Gorka Maiztegi <gorkamaiztegi@gik.blue>

#ARG AS_UID=33

ENV BASE_DIR /var/www/app
ENV APP_ENV ${APP_ENV:-dev}
ENV APP_DEBUG ${APP_DEBUG:-1}

#Modify UID of www-data into UID of local user
#RUN usermod -u ${AS_UID} www-data

# Operate as www-data in SYLIUS_DIR per default
WORKDIR ${BASE_DIR}

# nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY . ${BASE_DIR}

RUN chmod +x bin/console \
    && bin/console cache:warmup \
    && chown -R nginx:nginx .

EXPOSE 80
