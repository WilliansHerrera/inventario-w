# Inventario-W 🚀

Sistema de Gestión de Inventarios y Punto de Venta (POS) de alto rendimiento, construido con las tecnologías más modernas del ecosistema Laravel.

<p align="center">
    <img src="public/vendor/moonshine/logo-app.svg" width="200" alt="Inventario-W Logo">
</p>

## 🌟 Sobre el Proyecto

**Inventario-W** es una solución integral diseñada para empresas que requieren un control estricto de sus existencias, múltiples puntos de venta y una gestión administrativa centralizada. Reconstruido sobre **Laravel 13**, este sistema aprovecha el **TALL Stack** para ofrecer una experiencia de usuario fluida, reactiva y moderna.

## 🛠️ Tecnologías Principales

- **Framework:** [Laravel 13](https://laravel.com)
- **Panel Administrativo:** [MoonShine 4](https://moonshine-software.com) (Personalizado con Boss Edition UI)
- **Frontend:** [Livewire 3](https://livewire.laravel.com) + [Alpine.js](https://alpinejs.dev)
- **Estilos:** [Tailwind CSS](https://tailwindcss.com)
- **Base de Datos:** MySQL / MariaDB
- **Gestión de Permisos:** [Spatie Permission](https://spatie.be/docs/laravel-permission)
- **Respaldos:** [Spatie Laravel Backup](https://spatie.be/docs/laravel-backup) con integración para Google Drive.

## 📦 Módulos del Sistema

- **🛒 Punto de Venta (POS):** Interfaz optimizada para ventas rápidas con soporte para lectores de códigos de barras.
- **📦 Inventario:** Control de stock en tiempo real, seguimiento de movimientos y soporte multi-sucursal.
- **💰 Gestión de Cajas:** Control de múltiples cajas, apertura y cierre de turnos, y registro de movimientos de efectivo.
- **👥 Usuarios y Roles:** Sistema robusto de permisos para administradores, cajeros y personal de inventario.
- **🏢 Proveedores y Compras:** Gestión de cadena de suministro y entrada de mercancía.
- **📊 Reportes y Métricas:** Dashboard con indicadores clave de rendimiento (KPIs).
- **🌍 Multi-idioma:** Soporte integrado para múltiples regiones (Español, Inglés, etc.).

## 📋 Requisitos del Sistema

- PHP 8.3 o superior
- MySQL 8.0+
- Composer
- Node.js & NPM

## 🚀 Instalación

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/WilliansHerrera/inventario-w.git
   cd inventario-w
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Frontend:**
   ```bash
   npm install
   npm run build
   ```

4. **Configurar el entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *No olvides configurar tus credenciales de base de datos en el archivo `.env`.*

5. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Vincular el almacenamiento:**
   ```bash
   php artisan storage:link
   ```

7. **Iniciar el servidor:**
   ```bash
   php artisan serve
   ```

## 🔐 Acceso al Sistema

Una vez instalado, puedes acceder al panel administrativo en `/admin`. Los accesos por defecto (si ejecutaste los seeders) son:
- **Usuario:** `admin@admin.com`
- **Contraseña:** `password`

## 🛡️ Seguridad y Respaldos

El sistema incluye una tarea programada para realizar respaldos automáticos de la base de datos y archivos críticos. Puedes configurar tu cuenta de Google Drive en el archivo `.env` para que los respaldos se suban automáticamente a la nube.

---
Desarrollado con ❤️ por **Willians Herrera**.
