# Ruleta Dinámica PHP

Ruleta con la API [Wheel of Names](https://wheelofnames.com/).  
Desarrollado en **PHP**, **JavaScript** y **Bootstrap 5**.

## Descripción

Este proyecto implementa una ruleta dinámica conectada con la API de [wheelofnames.com](https://wheelofnames.com/), permitiendo girar y seleccionar nombres de manera aleatoria desde una interfaz web moderna y responsiva.

### Características principales

- Interfaz atractiva y responsiva usando Bootstrap 5.
- Lógica backend en **PHP**.
- Interactividad dinámica con **JavaScript**.
- Diseño personalizado usando **HTML** y **CSS**.
- Configuración con **Dockerfile** para implementación sencilla.

## Tecnologías utilizadas

- **PHP** (38.3%)
- **JavaScript** (24.5%)
- **HTML** (19.4%)
- **CSS** (15.4%)
- **Dockerfile** (2.4%)

## Instalación y ejecución

1. Clona el repositorio:
   ```bash
   git clone https://github.com/SaretoTiyer/ruleta-dinamica-php.git
   cd ruleta-dinamica-php
   ```
2. Instala las dependencias necesarias.
3. Ejecuta el proyecto localmente (usando Docker o servidor local):

   **Con Docker:**
   ```bash
   docker build -t ruleta-php .
   docker run -p 8080:80 ruleta-php
   ```

   **Sin Docker:**
   - Copia los archivos en tu servidor con soporte PHP.
   - Accede vía navegador a la carpeta principal.

## Uso

- Ingresa los nombres en la interfaz web.
- Haz clic en "Girar" para seleccionar uno al azar.

## Créditos

- API: [Wheel of Names](https://wheelofnames.com/)
- Desarrollado por [SaretoTiyer](https://github.com/SaretoTiyer).

## Licencia

Este proyecto está bajo la licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más detalles.
