FROM php:8.3-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev

# Extensiones PHP requeridas por Laravel
RUN docker-php-ext-install pdo_mysql mbstring zip bcmath

# Instalar Composer dentro del contenedor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar solo composer.* primero (optimiza cache)
COPY composer.json composer.lock ./

# Instalar dependencias PHP sin dev
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copiar el resto del proyecto
COPY . .

# Dar permisos correctos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Comando por defecto
CMD ["php-fpm"]

EXPOSE 9000
