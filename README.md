# 🚀 Inventario-w | Industrial POS & Inventory System
## Version 1.9.0-Beta (Latest Stable)

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  <img src="https://moonshine-laravel.com/logo.svg" width="150" alt="MoonShine Logo">
</p>

---

## 🌟 Overview
**Inventario-w** is a high-performance, industrial-grade Point of Sale (POS) and Inventory Management system. Built on **Laravel 12** and **MoonShine 4**, it offers a complete ecosystem for modern businesses, combining a robust web administrative panel with lightweight native Windows terminals.

The system is optimized for **offline-first** operations and **high-speed auditing**, ensuring that business owners have full control over their cash flow and stock at all times.

---

## 🛠️ Tech Stack
- **Web Framework:** [Laravel 12.x](https://laravel.com)
- **Admin Panel:** [MoonShine 4.x](https://moonshine-laravel.com)
- **Desktop Engine:** [Tauri 2.0](https://tauri.app) (Rust + JavaScript)
- **Mobile Support:** Android App (Kotlin + Jetpack Compose)
- **Database:** MySQL (Cloud) + SQLite (Local Edge Sync)
- **UI Architecture:** Alpine.js + TailwindCSS (Premium Aesthetic)

---

## ✨ Key Features (v1.9.0-Beta)

### 🏦 Advanced Cash Auditing (New)
- **Shift Management (Turnos):** Comprehensive control over opening and closing jornadas.
- **Physical Count (Arqueos):** Track differences between expected balance and real cash in drawer.
- **Expense Tracking (Egresos):** Register expenses and withdrawals (Proveedores, Servicios, etc.) directly from the POS or Admin panel.
- **Global Store Start:** One-click "Iniciar Jornada Única" to open all cash registers simultaneously.

### 🛒 Premium Industrial POS
- **High-Speed Search:** Instant product lookup by Name, SKU, or Barcode (optimized for scanners).
- **Auto-Open Shifts:** Smart logic that automatically initializes a shift upon the first sale if the register is closed.
- **Thermal Printing:** Professional receipts with customizable branding and dynamic numbering.
- **Offline-First (Win):** Native Windows terminal works without internet, syncing automatically when connectivity returns.

### 📦 Inventory & Logistics
- **Multi-Locale Management:** Independent stock levels for branches, warehouses, or delivery trucks.
- **Real-Time Adjustments:** Automatic stock discounts and movement logs for every sale or adjustment.
- **Low-Stock Alerts:** Visual indicators and monitoring for fast-moving items.

### ⚙️ Automation & Updates
- **GitHub Update Center:** Direct integration with GitHub API to monitor commits and releases.
- **One-Click Web Update:** Execute updates and database migrations directly from the admin interface.
- **Factory Reset Protection:** Secure data cleanup feature with password verification for "Fresh Start" scenarios.

---

## 🚀 Getting Started

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/WilliansHerrera/inventario-w.git
   cd inventario-w
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Stabilization:**
   ```bash
   # Create database first, then run migrations and audit seeders
   php artisan migrate --seed
   php artisan db:seed --class=CashAuditSeeder
   ```

5. **Run the server:**
   ```bash
   php artisan serve
   ```

### 🖥️ Windows Terminal Build
The installer is generated via **GitHub Actions** or **Local Scripts**:
1. Go to `POS-Windows` folder.
2. Run `.\prepare_installer.ps1` to bundle assets and generate the `.exe`.

---

## 👤 Author
**Willians Herrera**
- 📧 [williansherrera@gmail.com](mailto:williansherrera@gmail.com)
- 💼 Freelance Full-Stack Developer
- 🎯 Specialized in Industrial POS & AI Solutions

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
