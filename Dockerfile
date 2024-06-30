
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev

RUN docker-php-ext-install pdo_sqlite zip



# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


COPY ./app /app
WORKDIR /app




RUN composer install

EXPOSE 9000

RUN ls -la

RUN mkdir /data
RUN touch /data/database.db && chmod 777 /data/database.db


CMD ["php", "startup.php"]