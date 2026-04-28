FROM php:8.2-cli-alpine

# Dépendances système
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    bash

# Extensions PHP (pdo_pgsql pour PostgreSQL sur Render, pdo_mysql pour local)
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier le code
COPY . .

# Installer les dépendances PHP (prod)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions sur storage et cache
RUN chmod -R 775 storage bootstrap/cache

# Render injecte $PORT dynamiquement — EXPOSE est indicatif seulement
EXPOSE 10000

# Lancer le script de démarrage (migrations + seed + serve)
CMD ["bash", "bootstrap_render.sh"]
