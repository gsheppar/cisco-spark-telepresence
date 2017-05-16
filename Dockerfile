FROM php:7.0-apache
COPY /cisco-spark-telepresence/ /var/www/html/

RUN apt-get -yqq update
RUN apt-get install -yqq python
RUN apt-get -yqq install python-pip

EXPOSE 80
EXPOSE 443