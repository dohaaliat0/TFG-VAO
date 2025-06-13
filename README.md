
# 🏐 VAO - Gestión de Torneos y Partidos de Volleyball

Bienvenido a **VAO** (Volleyball Administration Organizer), una plataforma web construida con **Laravel** y tecnologías modernas para gestionar torneos, partidos, equipos y jugadores de volleyball.

---

## 📦 Requisitos del sistema

Antes de comenzar, asegúrate de tener instalado lo siguiente:

- PHP >= 8.1
- Composer
- MySQL o PostgreSQL
- Node.js >= 18.x
- NPM
- Laravel CLI (opcional)

---

## 🚀 Instalación del proyecto

Sigue estos pasos para configurar el entorno de desarrollo en tu máquina local.

### 1. Clona el repositorio

```bash
git clone https://github.com/tu-usuario/vao.git
cd vao
```

### 2. Instala las dependencias de PHP

```bash
composer install
```

### 3. Instala las dependencias de JavaScript

```bash
npm install
```

---

## ⚙️ Configuración del entorno

### 4. Configura las variables de entorno

```bash
cp .env.example .env
```

Edita el archivo `.env` y ajusta los valores de conexión a base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vao
DB_USERNAME=root
DB_PASSWORD=secret
```

### 5. Genera la clave de aplicación

```bash
php artisan key:generate
```

---

## 🗃️ Base de datos

### 6. Crea la base de datos

Asegúrate de tener una base de datos vacía con el nombre definido en tu archivo `.env` (por ejemplo, `vao`).

### 7. Ejecuta las migraciones con seeders

```bash
php artisan migrate:fresh --seed
```

Esto:
- Elimina las tablas si existen
- Ejecuta todas las migraciones
- Carga los datos de prueba definidos en los seeders

---

## 🛠️ Compilación del proyecto

### 8. Compila los assets frontend

```bash
npm run build
```

Esto generará los archivos CSS y JS listos para producción usando **Vite**.

### 9. (Opcional) Ejecuta el build de Composer

```bash
composer build
```

> Asegúrate de que el archivo `composer.json` tenga un script `"build"` definido. Por ejemplo:

```json
"scripts": {
  "build": [
    "@php artisan config:cache",
    "@php artisan route:cache",
    "@php artisan view:cache"
  ]
}
```

---

## 🔧 Servidor de desarrollo

### 10. Levanta el servidor local

```bash
php artisan serve
```

Accede a la app en tu navegador:

```
http://127.0.0.1:8000
```

---

## 📂 Estructura del proyecto

| Carpeta              | Descripción                                     |
|----------------------|-------------------------------------------------|
| `app/Models`         | Modelos Eloquent del sistema                    |
| `routes/web.php`     | Rutas web de Laravel                            |
| `resources/views`    | Vistas Blade (interfaz)                         |
| `resources/js`       | Código JavaScript del frontend (Vue, etc.)     |
| `database/migrations`| Migraciones para crear las tablas               |
| `database/seeders`   | Seeders con datos de prueba                     |
| `public/`            | Archivos públicos y punto de entrada `index.php`|

---

## 🧪 Comandos útiles

| Comando                             | Acción                                                                 |
|-------------------------------------|------------------------------------------------------------------------|
| `php artisan serve`                 | Inicia el servidor de desarrollo                                       |
| `php artisan migrate:fresh --seed` | Refresca y rellena la base de datos desde cero                         |
| `npm run build`                     | Compila el frontend para producción                                    |
| `composer build`                    | Ejecuta scripts personalizados definidos en `composer.json`            |

---

## 🧑‍💻 Autor

Desarrollado con pasión por el equipo de **VAO**.

> ¡Gracias por usar VAO! Si tienes sugerencias o encuentras algún error, no dudes en abrir un _issue_.

---

## 📜 Licencia

Este proyecto está licenciado bajo la [MIT License](LICENSE).
