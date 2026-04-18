# 🚀 Inventario-w | Industrial POS & Inventory System
## Version 2.0.0 (Premium Audit - PRUEBA)

> [!IMPORTANT]
> **ESTADO DE LA VERSIÓN:** Esta es una entrega de **PRUEBA (Beta)** diseñada para validar la estabilidad de la arquitectura **MoonShine 4** y el nuevo motor de auditoría. Incluye una firma digital dummy para pruebas de sincronización.

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  <img src="https://moonshine-laravel.com/logo.svg" width="150" alt="MoonShine Logo">
</p>

---

## 🌟 Overview
**Inventario-w** es un ecosistema industrial de alto rendimiento para el Punto de Venta (POS) y la gestión de inventarios. Desarrollado sobre **Laravel 12** y **MoonShine 4**, combina un panel administrativo web avanzado con terminales nativas ligeras para Windows diseñadas para operar sin conexión.

La versión **2.0.0 (Premium Audit)** introduce un sistema de auditoría financiera de grado industrial ("Zero-Loss") que garantiza el control total del flujo de caja.

---

## 🛠️ Tech Stack
- **Web Framework:** [Laravel 12.x](https://laravel.com)
- **Admin Panel:** [MoonShine 4.x](https://moonshine-laravel.com) (Page-based Architecture)
- **Desktop Engine:** [Tauri 2.x](https://tauri.app) (Rust + Node.js)
- **Database:** MySQL (Admin) + SQLite (Local Sync)
- **Real-time UI:** Alpine.js + TailwindCSS (Premium Aesthetic)

---

## ✨ Key Features (v2.0.0)

### 🏦 Auditoría de Turnos (Financial Core)
- **Arqueo Asistido:** Resumen dinámico en tiempo real de ventas, egresos y saldo esperado antes de cerrar la jornada.
- **Desglose de Denominaciones:** Calculadora integrada de billetes y monedas para auditorías precisas.
- **Gestión de Egresos:** Registro de gastos y retiros (`PROVEEDORES`, `SERVICIOS`) directo desde el POS.
- **Arquitectura de Páginas:** Detalle de auditoría refactorizado con bloques visuales (`Box`) para mayor claridad financiera.

### 🛒 Terminal POS Industrial
- **Búsqueda Instantánea:** Indexación optimizada por SKU, Nombre o Código de Barras.
- **Sincronización Inteligente:** Soporte para firmas digitales (Dummy en esta versión) para actualizaciones automáticas seguras.
- **Auto-Open Shifts:** Inicialización inteligente de turnos con la primera venta.

### ⚙️ Automatización
- **One-Click Update:** Sistema de actualización y migración desde el panel administrativo.
- **Factory Reset:** Protección de datos y reinicio seguro del sistema.

---

## 🚀 Instalación Rápida

### 1. Servidor Web (Laravel)
```bash
# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Migraciones y Seeders (Crucial para auditoría)
php artisan migrate --seed
php artisan db:seed --class=CashAuditSeeder
```

### 🖥️ Windows Terminal (Tauri)
Para generar el instalador nativo de la versión 2.0.0:
1. Abre una terminal de PowerShell como administrador.
2. Ejecuta el script de compilación automatizada:
   ```powershell
   .\POS-Windows\build.ps1
   ```
3. El instalador generado se ubicará en: `storage/app/public/pos/POS-Setup.msi`

---

## 👤 Autor
**Willians Herrera**
- 📧 [williansherrera@gmail.com](mailto:williansherrera@gmail.com)
- 🎯 Especialista en Soluciones POS Industriales & IA

---

## 📄 Licencia
Este proyecto es software de código abierto bajo la licencia [MIT](LICENSE).
