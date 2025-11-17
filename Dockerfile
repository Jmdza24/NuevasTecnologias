FROM php:8.3-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libonig-dev \
    libzip-dev \
    libpng-dev

# Extensiones de PHP necesarias para Laravel
RUN docker-php-ext-install pdo_mysql mbstring zip

# Instalar Composer dentro del contenedor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto
COPY . /var/www

# Definir la carpeta de trabajo
WORKDIR /var/www

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Permisos
RUN chown -R www-data:www-data /var/www

CMD ["php-fpm"]

EXPOSE 9000
# Fin del Dockerfile