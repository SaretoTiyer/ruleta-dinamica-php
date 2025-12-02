# Usamos una imagen oficial que trae Apache y PHP juntos
FROM php:8.2-apache

# Instalamos las extensiones necesarias para conectar a MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitamos mod_rewrite (útil para URLs limpias, aunque opcional para tu caso actual)
RUN a2enmod rewrite

# Copiamos tus archivos al directorio público de Apache
COPY . /var/www/html/

# Ajustamos los permisos para que Apache pueda leer/escribir
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Le decimos a Railway que este contenedor escucha en el puerto 80
EXPOSE 80
