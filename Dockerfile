# Usa una imagen base con PHP 8.x FPM (FastCGI Process Manager)
FROM php:8.3-fpm-alpine

# Instala el servidor web Nginx y las herramientas necesarias
RUN apk add --no-cache nginx supervisor wget

# Instala la extensión MySQLi que tu API necesita
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Crea directorios necesarios
RUN mkdir -p /run/nginx /var/log/nginx /var/www/html

# ----------------- CONFIGURACIÓN NGINX -----------------
# Copia una configuración básica de Nginx para PHP
COPY default.conf /etc/nginx/conf.d/default.conf

# ----------------- CONFIGURACIÓN SUPERVISOR -----------------
# Supervisor gestiona la ejecución simultánea de Nginx y PHP-FPM
COPY supervisord.conf /etc/supervisord.conf

# ----------------- CÓDIGO DE LA APLICACIÓN -----------------
# Copia todo el código del repositorio al directorio raíz del servidor
COPY . /var/www/html

# Establece los permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto por defecto de Railway
EXPOSE 8080

# Inicia Supervisor (que inicia Nginx y PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
