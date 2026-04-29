# 🚀 Inventario-w | Industrial POS & Inventory System
## Version 2.2.0 (Express & Industrial Cash Management + POS Security PIN)

> [!IMPORTANT]
> **ESTADO DE LA VERSIÓN:** Esta versión habilita los **Modos de Gestión de Efectivo** y la **Autenticación por PIN** en el POS. Se ha mejorado la seguridad y la flexibilidad operativa.

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  <img src="https://moonshine-laravel.com/logo.svg" width="150" alt="MoonShine Logo">
</p>

---

## 🌟 Overview
**Inventario-w** es un ecosistema industrial de alto rendimiento para el Punto de Venta (POS) y la gestión de inventarios. Desarrollado sobre **Laravel 12** y **MoonShine 4**, combina un panel administrativo web avanzado con terminales nativas ligeras para Windows diseñadas para operar sin conexión.

La versión **2.2.0** introduce la flexibilidad de operar en modo Express o Industrial y añade una capa de seguridad mediante PIN para los cajeros.

---

## 🛠️ Tech Stack
- **Web Framework:** [Laravel 12.x](https://laravel.com)
- **Admin Panel:** [MoonShine 4.x](https://moonshine-laravel.com) (Page-based Architecture)
- **Desktop Engine:** [Tauri 2.x](https://tauri.app) (Rust + Node.js)
- **Database:** MySQL (Admin) + SQLite (Local Sync)
- **Real-time UI:** Alpine.js + TailwindCSS (Premium Aesthetic)

---

## ✨ Key Features (v2.2.0)

### 🛡️ Seguridad POS (New)
- **Autenticación por PIN:** Acceso rápido y seguro para cajeros mediante un PIN de 4 dígitos.
- **Gestión Centralizada:** Asignación y actualización de PINs desde el panel administrativo MoonShine.

### ⚙️ Modos de Gestión de Efectivo (New)
- **Modo Express:** Operación simplificada de caja única ideal para tiendas pequeñas.
- **Modo Industrial:** Control avanzado multi-caja, multi-turno y arqueos detallados.

### 📦 Gestión de Inventario
- **Recepción de Compras:** Interfaz nativa en MoonShine 4 para la carga de productos desde proveedores.
- **Procesamiento de Stock:** Algoritmo de ingreso automático que actualiza existencias en sucursales.
- **Historial de Costos:** Rastreo automático de cambios en el precio de compra.

### 🏦 Auditoría de Turnos
- **Arqueo Asistido:** Resumen dinámico en tiempo real de ventas, egresos y saldo esperado.
- **Desglose de Denominaciones:** Calculadora integrada de billetes y monedas.

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

# Migraciones y Seeders
php artisan migrate --seed
```

### 🖥️ Windows Terminal (Tauri)
Para generar el instalador nativo de la versión 2.2.0:
1. Abre una terminal de PowerShell como administrador.
2. Ejecuta el script de compilación automatizada:
   ```powershell
   .\POS-Windows\build.ps1
   ```
3. El instalador generado se ubicará en: `storage/app/public/pos/POS-Setup.exe`

---

## 👤 Autor
**Willians Herrera**
- 📧 [williansherrera@gmail.com](mailto:williansherrera@gmail.com)
- 🎯 Especialista en Soluciones POS Industriales & IA

---

## 📄 Licencia
Este proyecto es software de código abierto bajo la licencia [MIT](LICENSE).
