FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www
RUN chown -R www:www /var/www/html
USER www

WORKDIR /var/www/html

EXPOSE 80/tcp
EXPOSE 9000

CMD ["php-fpm"]