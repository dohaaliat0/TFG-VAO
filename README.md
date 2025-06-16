
# 🚀 Proyecto Laravel con Docker Compose (Linux)

Este proyecto Laravel está configurado para ejecutarse usando Docker Compose en sistemas Linux. Aquí encontrarás los pasos necesarios para arrancar y gestionar tu proyecto.

---

## 📌 Requisitos

Antes de comenzar, asegúrate de tener instalado en tu sistema:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Node.js](https://nodejs.org/)
- [npm](https://www.npmjs.com/)
- [Composer](https://getcomposer.org/)

---

### 2️⃣ Acceder al directorio del proyecto

```bash
cd nombre_proyecto
```

---

### 3️⃣ Levantar los contenedores con Docker Compose

```bash
./vendor/bin/sail up -d
```

Esto iniciará los contenedores (Laravel, MySQL, Mailpit, etc).

---

### 4️⃣ Ejecutar las migraciones

```bash
./vendor/bin/sail artisan migrate
```

Esto creará las tablas necesarias en la base de datos.

---

### 5️⃣ Instalar dependencias frontend (si usas Vite o similar)

```bash
npm install
npm run build
```

---

## ⚙️ Comandos útiles

✅ **Arrancar los contenedores**
```bash
./vendor/bin/sail up -d
```

✅ **Parar los contenedores**
```bash
./vendor/bin/sail down
```

✅ **Reiniciar los contenedores**
```bash
./vendor/bin/sail restart
```

✅ **Acceder al shell del contenedor**
```bash
./vendor/bin/sail shell
```

✅ **Ejecutar comandos Artisan**
```bash
./vendor/bin/sail artisan <comando>
```
Ejemplo:
```bash
./vendor/bin/sail artisan migrate
```

✅ **Ejecutar Composer**
```bash
./vendor/bin/sail composer <comando>
```
Ejemplo:
```bash
./vendor/bin/sail composer require laravel/sanctum
```

✅ **Ejecutar npm**
```bash
./vendor/bin/sail npm <comando>
```
Ejemplo:
```bash
./vendor/bin/sail npm run dev
```

---

## 🌐 Acceso al Proyecto

Una vez que los contenedores están en marcha, abre tu navegador y accede a:

```
http://localhost
```

---

## 📝 Notas

- Puedes modificar el archivo `docker-compose.yml` para personalizar los servicios según tus necesidades.
- Si quieres exponer otros puertos o servicios, recuerda actualizar también el archivo `.env` y el `docker-compose.yml`.
- Para detener todo el entorno, ejecuta `./vendor/bin/sail down`.

---

## 🚀 ¡Listo para desarrollar!

Tu proyecto Laravel está corriendo en Docker con todos los servicios necesarios.  
¡A construir cosas increíbles! 🎉
