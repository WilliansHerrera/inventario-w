# Build script for POS-Windows (Tauri)
# Run this on your local Windows machine with Rust and Node.js installed.

Write-Host "--- Iniciando proceso de Compilación Automática POS-Windows ---" -ForegroundColor Cyan

# 1. Verificar dependencias
if (!(Get-Command npm -ErrorAction SilentlyContinue)) {
    Write-Error "NPM no encontrado. Por favor, instala Node.js."
    exit
}

if (!(Get-Command cargo -ErrorAction SilentlyContinue)) {
    Write-Error "Rust/Cargo no encontrado. Por favor, instala Rust (rustup.rs)."
    exit
}

# 2. Instalar dependencias del proyecto
Write-Host ">> Instalando dependencias de Node..." -ForegroundColor Yellow
npm install --prefix POS-Windows

# 3. Compilar el proyecto Tauri
Write-Host ">> Compilando el binario .EXE (Esto tardará unos minutos la primera vez)..." -ForegroundColor Yellow
npm run --prefix POS-Windows tauri build

# 4. Mover el instalador a la carpeta storage de Laravel
$sourceMsi = Get-ChildItem -Path "POS-Windows\src-tauri\target\release\bundle\msi\*.msi" | Select-Object -First 1
$sourceExe = Get-ChildItem -Path "POS-Windows\src-tauri\target\release\*.exe" | Select-Object -First 1 # Depende de la config de Tauri

# Buscamos el instalador generado
$outputDir = "storage\app\public\pos"
if (!(Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir
}

if ($sourceMsi) {
    Copy-Item $sourceMsi.FullName -Destination "$outputDir\POS-Setup.msi"
    Write-Host ">> ¡Éxito! Instalador MSI copiado a: $outputDir\POS-Setup.msi" -ForegroundColor Green
} else {
    Write-Warning "No se encontró el instalador .MSI. Verifica la salida de tauri build."
}

Write-Host "--- Proceso finalizado ---" -ForegroundColor Cyan
