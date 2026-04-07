# 🚀 Inventario-w | Industrial POS & Inventory System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  <img src="https://moonshine-laravel.com/logo.svg" width="150" alt="MoonShine Logo">
</p>

---

## 🌟 Overview
**Inventario-w** is a high-performance, industrial-grade Point of Sale (POS) and Inventory Management system. Built on **Laravel 12** and **MoonShine 4**, it offers a complete ecosystem for modern businesses, combining a robust web administrative panel with lightweight native Windows terminals.

---

## 🛠️ Tech Stack
- **Web Framework:** [Laravel 12.x](https://laravel.com)
- **Admin Panel:** [MoonShine 4.x](https://moonshine-laravel.com)
- **Desktop Engine:** [Tauri 2.0](https://tauri.app) (Rust + JavaScript)
- **Mobile Support:** Android App (Kotlin + Jetpack Compose)
- **Database:** MySQL (Cloud) + SQLite (Local Edge Sync)

---

## ✨ Key Features

### 🛒 Industrial POS (Web & Win)
- **High-Speed Search:** Instant product lookup by Name, SKU, or Barcode.
- **Offline-First (Win):** Native Windows terminal works without internet, syncing automatically.
- **Smart Numpad:** Optimized for rapid, keyboard-only operation in physical stores.
- **Thermal Printing:** Professional receipts with customizable branding.

### 📦 Inventory Control
- **Multi-Locale Management:** Independent stock levels for branches, warehouses, or delivery trucks.
- **Low-Stock Alerts:** Real-time monitoring and visual indicators.

### ⚙️ Automation & Updates (New)
- **GitHub Update Center:** Direct integration with GitHub API to monitor commits and releases.
- **One-Click Web Update:** Execute `git pull` and database migrations directly from the admin panel.
- **POS Release Sync:** Automatically fetch and manage `.exe` installers from GitHub Releases.
- **Dynamic Themes:** UI customization with real-time palette switching (Green, Cyan, Purple, etc.) from global settings.

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

4. **Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Run the server:**
   ```bash
   php artisan serve
   ```

### 🖥️ Windows Terminal Build
The installer is generated via **GitHub Actions**. To build locally:
1. Go to `POS-Windows` folder.
2. Run `.\build.ps1`.

---

## 👤 Author
**Willians Herrera**
- 📧 [williansherrera@gmail.com](mailto:williansherrera@gmail.com)
- 💼 Freelance Full-Stack Developer
- 🎯 Specialized in Industrial POS & AI Solutions

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
