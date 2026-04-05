---
name: native-pos-windows
description: Guía para el desarrollo de aplicaciones POS nativas en Windows utilizando Tauri, Rust y conexión a periféricos.
---

# Desarrollo de POS Nativo para Windows (Tauri + Rust)

Esta skill proporciona las directrices para construir una interfaz de Punto de Venta (POS) que sea extremadamente ligera, rápida y tenga acceso directo al hardware en Windows.

## 1. Stack Tecnológico Recomendado
- **Frontend:** HTML, CSS (Tailwind), JS (Alpine.js o React/Vue).
- **Backend de App:** Tauri (Rust).
- **Base de Datos Local:** SQLite (vía `tauri-plugin-sql`).
- **Comunicación:** Acceso a `window.__TAURI__` para invocar comandos de Rust.

## 2. Integración de Hardware POS
### Impresión Térmica (ESC/POS)
No utilices `window.print()`. En su lugar, usa comandos de Rust para enviar datos binarios directamente al puerto:
```rust
// Ejemplo conceptual en Rust para Tauri
#[tauri::command]
fn print_receipt(content: String) {
    // Lógica para enviar a USB/LPT1/Serial
}
```

## 3. Optimización para Windows
- **WebView2:** Asegurarse de que el Runtime esté instalado (Tauri lo gestiona).
- **Modo Kiosko:** Configurar la ventana principal para que sea a pantalla completa y sin bordes para cajeros.
- **Rendimiento:** Mantener el binario por debajo de 15MB.

## 4. Comandos de Inicialización
```bash
npx tauri init
npm install @tauri-apps/api
# Agregar plugins necesarios en src-tauri/Cargo.toml
```
