FROM php:8.3-fpm 

# Instala servidor web, supervisor y herramientas (Usando apt para Debian)
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Instala la extensión MySQLi y PDO
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
