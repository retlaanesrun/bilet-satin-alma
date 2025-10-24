FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libsqlite3-dev zip unzip git && \
    docker-php-ext-install pdo pdo_sqlite && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy source
COPY . /var/www/html

# Basic php.ini
RUN echo "display_errors=1" > /usr/local/etc/php/conf.d/dev.ini && \
    echo "error_reporting=E_ALL" >> /usr/local/etc/php/conf.d/dev.ini

# Permissions for SQLite database directory
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

USER www-data

CMD ["php-fpm"]
