FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    msmtp \
    msmtp-mta \
    ca-certificates \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip

# Habilitar mod_rewrite para Apache (.htaccess) y silenciar advertencia ServerName
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar código de la aplicación (modo producción sin bind mounts)
COPY . /var/www/html

# Ajustar permisos de runtime para Apache/PHP
RUN chown -R www-data:www-data /var/www/html

# Entrypoint: configura SMTP/msmtp a partir de variables de entorno.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exponer el puerto 80
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
