# run the following commands using docker-compose:
# docker-compose -f docker-compose.yml up
# docker-compose -f docker-compose.yml build

FROM ubuntu:latest
ENV DEBIAN_FRONTEND=interactive
RUN apt-get update -yqq && \
  apt-get upgrade -yqq && \
  apt-get install php libapache2-mod-php php-mysql php-gd php-mbstring php-xml php-curl php-common php-json composer -yqq && \
  apt-get install mysql-client libmysqlclient-dev -yqq && \
  apt auto-remove -yqq

RUN rm -rf /var/www/*
COPY ./app /var/www/html
COPY ./app/protected/config/main.php.docker /var/www/html/protected/config/main.php

RUN chmod 0777 /var/www/html/assets
RUN chmod 0777 /var/www/html/protected/runtime
RUN sed -i 's#AllowOverride [Nn]one#AllowOverride All#' /etc/apache2/apache2.conf
RUN a2enmod rewrite

EXPOSE 80
EXPOSE 443
CMD ["apachectl","-D","FOREGROUND"]
