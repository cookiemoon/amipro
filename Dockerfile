# Dockerfile

FROM php:8.4-apache

# Install dependencies and extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache's rewrite module
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

RUN composer install --verbose

# --- Automatic Permissions Fix ---
RUN echo '#!/bin/sh' > /usr/local/bin/entrypoint.sh && \
    echo 'HOST_UID=$(stat -c %u /var/www/html)' >> /usr/local/bin/entrypoint.sh && \
    echo 'HOST_GID=$(stat -c %g /var/www/html)' >> /usr/local/bin/entrypoint.sh && \
    echo 'usermod -u $HOST_UID www-data' >> /usr/local/bin/entrypoint.sh && \
    echo 'groupmod -g $HOST_GID www-data' >> /usr/local/bin/entrypoint.sh && \
    echo 'exec "$@"' >> /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]