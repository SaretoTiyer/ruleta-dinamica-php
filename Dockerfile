# Usamos la imagen base de Apache con PHP
FROM php:8.2-apache

# Instalamos extensiones para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitamos mod_rewrite
RUN a2enmod rewrite

# --- CORRECCIÓN DEL PUERTO ---
# Cambiamos la configuración de Apache para que escuche en el puerto 8080
# en lugar del 80. Esto alinea Apache con lo que Railway espera por defecto.
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copiamos los archivos
COPY . /var/www/html/

# Ajustamos permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Le decimos a Railway que escuche en el 8080
EXPOSE 8080
