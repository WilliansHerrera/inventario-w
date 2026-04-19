# 🚀 Inventario-w | Industrial POS & Inventory System
## Version 2.1.0 (Inventory Update)

> [!IMPORTANT]
> **ESTADO DE LA VERSIÓN:** Esta versión habilita el **Motor de Inventario Real**. Se han estabilizado las recepciones de compra y el impacto directo en el stock de sucursales.

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  <img src="https://moonshine-laravel.com/logo.svg" width="150" alt="MoonShine Logo">
</p>

---

## 🌟 Overview
**Inventario-w** es un ecosistema industrial de alto rendimiento para el Punto de Venta (POS) y la gestión de inventarios. Desarrollado sobre **Laravel 12** y **MoonShine 4**, combina un panel administrativo web avanzado con terminales nativas ligeras para Windows diseñadas para operar sin conexión.

La versión **2.1.0** añade el control de ingreso de mercancía y la estabilización financiera mediante el uso de punto decimal estándar.

---

## 🛠️ Tech Stack
- **Web Framework:** [Laravel 12.x](https://laravel.com)
- **Admin Panel:** [MoonShine 4.x](https://moonshine-laravel.com) (Page-based Architecture)
- **Desktop Engine:** [Tauri 2.x](https://tauri.app) (Rust + Node.js)
- **Database:** MySQL (Admin) + SQLite (Local Sync)
- **Real-time UI:** Alpine.js + TailwindCSS (Premium Aesthetic)

---

## ✨ Key Features (v2.1.0)

### 📦 Gestión de Inventario (New)
- **Recepción de Compras:** Interfaz nativa en MoonShine 4 para la carga de productos desde proveedores.
- **Procesamiento de Stock:** Algoritmo de ingreso automático que actualiza existencias en sucursales al confirmar facturas.
- **Historial de Costos:** Rastreo automático de cambios en el precio de compra para análisis de márgenes.
- **Seguridad Inmutable:** Bloqueo de facturas procesadas para evitar alteraciones contables tras el ingreso.

### 🏦 Auditoría de Turnos (Zero-Loss)
- **Arqueo Asistido:** Resumen dinámico en tiempo real de ventas, egresos y saldo esperado antes de cerrar la jornada.
- **Desglose de Denominaciones:** Calculadora integrada de billetes y monedas para auditorías precisas.
- **Gestión de Egresos:** Registro de gastos y retiros (`PROVEEDORES`, `SERVICIOS`) directo desde el POS.

### 🪙 Estandarización Financiera
- **Formato Universal:** Uso del punto `.` como separador decimal en toda la plataforma para reportes técnicos precisos.
- **Cálculos del Lado del Servidor:** Integridad garantizada mediante eventos de modelo para totales y subtotales.
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
