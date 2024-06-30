
# install apache, php and mysql
FROM php:8.3-apache
COPY app/ /var/www/html/
RUN rm -rf /var/www/html/tests

# copy .env file to the root directory
COPY .env /var/www/html/.env

# dont allow to access .env file from browser
RUN echo "RedirectMatch 404 /\.env" > /var/www/html/.htaccess
