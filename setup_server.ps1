<#
.SYNOPSIS
    Script de Instalación Automatizada para Servidor Inventario-W (Laravel).
    Este script descarga e instala PHP, MySQL, Git y Composer, y configura la aplicación.

.DESCRIPTION
    Ejecutar este script en una terminal de PowerShell como ADMINISTRADOR.
    Versión: 1.0
    Autor: Antigravity AI
#>

# 1. Verificar Privilegios de Administrador
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "Este script DEBE ejecutarse como ADMINISTRADOR. Por favor, abre PowerShell como administrador e inténtalo de nuevo."
    exit
}

Write-Host "--- Iniciando Instalación de Servidor Inventario-W ---" -ForegroundColor Cyan

# 2. Instalación de Dependencias vía Winget
Write-Host "Instalando PHP, MySQL, Git y Composer..." -ForegroundColor Yellow
winget install -e --id PHP.PHP --version 8.2.11 --silent --accept-package-agreements --accept-source-agreements
winget install -e --id Oracle.MySQL --silent --accept-package-agreements
winget install -e --id Git.Git --silent --accept-package-agreements
winget install -e --id Composer.Composer --silent --accept-package-agreements

# Actualizar variables de entorno para la sesión actual
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# 3. Configuración de PHP (Habilitar extensiones)
Write-Host "Configurando extensiones de PHP..." -ForegroundColor Yellow
$phpPath = split-path (where.exe php.exe)[0]
$phpIni = Join-Path $phpPath "php.ini"

if (-not (Test-Path $phpIni)) {
    Copy-Item (Join-Path $phpPath "php.ini-development") $phpIni
}

$exts = @("curl", "fileinfo", "gd", "mbstring", "openssl", "pdo_mysql", "bcmath")
foreach ($ext in $exts) {
    (Get-Content $phpIni) -replace "^;extension=$ext", "extension=$ext" | Set-Content $phpIni
}

# 4. Configuración de la Aplicación Laravel
Write-Host "Configurando la aplicación Laravel..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Archivo .env creado. Por favor, revisa las credenciales de base de datos." -ForegroundColor Gray
}

# Instalar dependencias de PHP
composer install --no-dev --optimize-autoloader

# Generar llave y enlace de storage
php artisan key:generate --force
php artisan storage:link

# 5. Configuración de Base de Datos
Write-Host "Intentando crear base de datos en MySQL..." -ForegroundColor Yellow
# Intentar crear la base de datos (Asumiendo root sin password por defecto en instalaciones nuevas)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS inventario_w CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Correr migraciones
php artisan migrate --force

# 6. Configuración de Auto-inicio (Tarea Programada)
Write-Host "Configurando autoinicio del servidor..." -ForegroundColor Yellow
$currentDir = Get-Location
$action = New-ScheduledTaskAction -Execute "php.exe" -Argument "artisan serve --host=0.0.0.0 --port=80" -WorkingDirectory $currentDir
$trigger = New-ScheduledTaskTrigger -AtLogOn
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries

Register-ScheduledTask -TaskName "InventarioW_Server" -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Force

Write-Host "--- Instalacion Completada con Éxito ---" -ForegroundColor Green
Write-Host "El servidor se iniciará automáticamente al iniciar sesión."
Write-Host "Puedes acceder en: http://localhost"
Write-Host "Presiona cualquier tecla para salir..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
