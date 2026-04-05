# 🚀 Inventario-w | Industrial POS & Inventory System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  <img src="https://moonshine-laravel.com/logo.svg" width="150" alt="MoonShine Logo">
</p>

---

## 🌟 Overview
**Inventario-w** is a high-performance, industrial-grade Point of Sale (POS) and Inventory Management system designed for efficiency and scalability. Built on the cutting-edge **Laravel 12** framework and powered by the **MoonShine 4** admin panel, it provides a seamless experience for managing sales, stock, and business operations.

Developed with a focus on **speed, reliability, and modern aesthetics**, this system is tailored for businesses that require robust inventory tracking across multiple locations.

---

## 🛠️ Tech Stack
- **Framework:** [Laravel 12.x](https://laravel.com)
- **Admin Panel:** [MoonShine 4.x](https://moonshine-laravel.com)
- **Frontend Tooling:** Vite + Alpine.js
- **Database:** MySQL / PostgreSQL / SQLite
- **Language:** PHP 8.2+

---

## ✨ Key Features

### 🛒 Advanced POS Module (Industrial V7)
- **High-Speed Search:** Instant product lookup by Name, SKU, or Barcode.
- **Smart Numpad Integration:** Optimized for rapid keyboard-first data entry.
- **Real-time Stock Validation:** Prevents selling items without sufficient inventory in the specific branch.
- **Thermal Printing:** Automated receipt generation with customizable headers and footers.

### 📦 Inventory & Logistics
- **Multi-Locale Support:** Manage stock levels independently across different warehouses or stores.
- **Automated Calculations:** Real-time stock adjustment upon sale completion.
- **SKU & Barcode Support:** Standardized product identification.

### 💰 Financial Management
- **Global Currency System:** Automatically formats prices based on your region's settings.
- **Cash Register (Cajas):** Track sales sessions, balances, and user-specific transactions.
- **Sales Analytics:** Comprehensive reports on sales performance and revenue.

### 🖥️ Native Windows Terminal (Beta)
- **Offline-First:** Funciona sin internet usando SQLite local.
- **Sincronización Inteligente:** Sincroniza productos y ventas automáticamente al detectar conexión.
- **Ultra-Ligera:** App nativa construida con Tauri (~10MB).
- **Seguridad por Token:** Cada terminal se autentica con su propio token único.

---

## ⚙️ Global Configuration
- **Customizable Branding:** Easily update your business logo, receipt headers, and footers.
- **Regional Settings:** Configure default currency, decimal precision, and language.

---

## 🚀 Getting Started

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL or alternative database

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/williansh/inventario-w.git
   cd inventario-w
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration:**
   Configure your `.env` file with your database credentials and run migrations:
   ```bash
   php artisan migrate --seed
   ```

5. **Build Assets:**
   ```bash
   npm run build
   ```

6. **Serve the Application:**
   ```bash
   php artisan serve
   ```

### 🖥️ Windows Terminal Build
El instalador `.exe` se genera automáticamente mediante **GitHub Actions** al subir cambios. También puedes compilarlo localmente:
1. Ve a la carpeta `POS-Windows`.
2. Ejecuta `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass; .\build.ps1`.

---

## 👤 Author
**Willians Herrera**
- 📧 Email: [williansherrera@gmail.com](mailto:williansherrera@gmail.com)
- 💼 Professional Web Developer
- 🎯 Focused on Industrial Software Solutions

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
