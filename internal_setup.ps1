<#
.SYNOPSIS
    Script de Configuración Interna para el Instalador EXE.
    Se ejecuta automáticamente después de extraer los archivos.
#>

$installDir = $args[0] # La ruta donde el usuario instalo
if (-not $installDir) { $installDir = "C:\Inventario-W" }
Set-Location $installDir

Write-Host "Configurando entorno offline en $installDir..." -ForegroundColor Cyan

# 0. Funcion para normalizar carpetas (subir archivos si hay una carpeta extra adentro)
function Normalize-Folder($path) {
    # Buscar carpetas que se parezcan a la extraccion (php-... o mysql-...)
    $subdirs = Get-ChildItem -Path $path -Directory
    foreach ($dir in $subdirs) {
        if ($dir.Name -like "*php*" -or $dir.Name -like "*mysql*") {
            Write-Host "Normalizando estructura en $path desde $($dir.Name)..." -ForegroundColor Gray
            Get-ChildItem -Path $dir.FullName | Move-Item -Destination $path -Force
            Remove-Item $dir.FullName -Recurse -Force
            return
        }
    }
}

# 1. Limpieza de Procesos y Servicios Previos (CRITICO para reinstalacion)
Write-Host "Limpiando instalaciones previas..." -ForegroundColor Yellow
# Detener tareas programadas
Unregister-ScheduledTask -TaskName "InventarioW_Web_Server" -Confirm:$false -ErrorAction SilentlyContinue
Unregister-ScheduledTask -TaskName "InventarioW_DB_Server" -Confirm:$false -ErrorAction SilentlyContinue

# Matar procesos si estan corriendo
taskkill /F /IM php.exe /T 2>$null
taskkill /F /IM mysqld.exe /T 2>$null
Start-Sleep -Seconds 2

# 2. Descomprimir Motores
Write-Host "Extrayendo motores..." -ForegroundColor Yellow
function Force-Remove-Folder($path) {
    if (Test-Path $path) {
        Write-Host "Borrando $path..." -ForegroundColor Gray
        Remove-Item $path -Recurse -Force -ErrorAction SilentlyContinue
        if (Test-Path $path) {
            # Si todavia existe, intentar renombrarla (a veces Windows bloquea carpetas por un tiempo)
            Rename-Item $path "$path.old.$(Get-Random)" -ErrorAction SilentlyContinue
        }
    }
}

Force-Remove-Folder "server\php"
Force-Remove-Folder "server\mysql"

Expand-Archive -Path "deps\php-8.2.zip" -DestinationPath "server\php" -Force
Normalize-Folder "server\php"

Expand-Archive -Path "deps\mysql-8.0.zip" -DestinationPath "server\mysql" -Force
Normalize-Folder "server\mysql"

# 2. Configurar PHP.ini
$phpIni = "server\php\php.ini"
Copy-Item "server\php\php.ini-development" $phpIni -Force
# Habilitar el directorio de extensiones (CRITICO para portables)
(Get-Content $phpIni) -replace "^;extension_dir = `"ext`"", "extension_dir = `"ext`"" | Set-Content $phpIni

$exts = @("curl", "fileinfo", "gd", "mbstring", "openssl", "pdo_mysql", "pdo_sqlite", "sqlite3", "bcmath")
foreach ($ext in $exts) {
    (Get-Content $phpIni) -replace "^;extension=$ext", "extension=$ext" | Set-Content $phpIni
}

# 3. Crear Configuracion MySQL (my.ini)
Write-Host "Configurando MySQL (my.ini)..." -ForegroundColor Yellow
$mysqlBase = (Join-Path $installDir "server\mysql").Replace('\', '/')
$mysqlData = (Join-Path $mysqlBase "data").Replace('\', '/')
$myIni = Join-Path $installDir "server\mysql\bin\my.ini"

$iniContent = @"
[mysqld]
basedir=`"$mysqlBase`"
datadir=`"$mysqlData`"
port=3306
bind-address=0.0.0.0
max_connections=100
character-set-server=utf8mb4
default-storage-engine=INNODB
"@
$iniContent | Set-Content $myIni -Encoding Ascii

# 4. Verificar Colision de Puertos (MySQL 3306)
$mysqlPort = 3306
$portCheck = Get-NetTCPConnection -LocalPort $mysqlPort -ErrorAction SilentlyContinue
if ($portCheck) {
    Write-Host "CRITICAL: Puerto 3306 ocupado. POR FAVOR APAGA XAMPP O CUALQUIER OTRO MYSQL." -ForegroundColor Red
    Write-Host "La instalacion fallara si el puerto no esta libre." -ForegroundColor Red
}

# 5. Inicializar MySQL (Capturando Errores con Rutas Absolutas Seguras)
Write-Host "Inicializando base de datos local..." -ForegroundColor Yellow
$mysqlLog = Join-Path $installDir "mysql_init_error.log"
if (Test-Path $mysqlData) { Remove-Item $mysqlData -Recurse -Force }

# Ejecutamos con la configuracion recien creada
Start-Process "server\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=`"$myIni`" --initialize-insecure --console" -NoNewWindow -Wait -RedirectStandardError $mysqlLog

# 6. Crear Tareas de Autoinicio (Servidor Web y Base de Datos)
Write-Host "Registrando servicios de fondo..." -ForegroundColor Yellow

$phpAction = New-ScheduledTaskAction -Execute "$installDir\server\php\php.exe" -Argument "artisan serve --host=0.0.0.0 --port=8000" -WorkingDirectory "$installDir\source"
$dbAction = New-ScheduledTaskAction -Execute "$installDir\server\mysql\bin\mysqld.exe" -Argument "--defaults-file=`"$installDir\server\mysql\bin\my.ini`" --console" -WorkingDirectory "$installDir\server\mysql"

$trigger = New-ScheduledTaskTrigger -AtLogOn
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

Register-ScheduledTask -TaskName "InventarioW_Web_Server" -Action $phpAction -Trigger $trigger -Principal $principal -Force
Register-ScheduledTask -TaskName "InventarioW_DB_Server" -Action $dbAction -Trigger $trigger -Principal $principal -Force

# 7. Iniciar Motor de Base de Datos para Migraciones
Write-Host "Iniciando motor de base de datos..." -ForegroundColor Yellow
Start-ScheduledTask -TaskName "InventarioW_DB_Server"
Write-Host "Esperando 10 segundos a que MySQL este listo..." -ForegroundColor Gray
Start-Sleep -Seconds 10

# 8. Crear Base de Datos
Write-Host "Creando base de datos 'inventario_w'..." -ForegroundColor Yellow
$mysqlCmd = Join-Path $mysqlBase "bin\mysql.exe"
Start-Process $mysqlCmd -ArgumentList "-u root -e 'CREATE DATABASE IF NOT EXISTS inventario_w;'" -Wait

# 9. Configurar Laravel (.env y Migraciones)
Set-Location "$installDir\source"
Copy-Item ".env.example" ".env" -Force

# Inyectar configuracion de MySQL en el .env
(Get-Content ".env") -replace "^APP_URL=.*", "APP_URL=http://localhost:8000" `
                     -replace "^DB_CONNECTION=.*", "DB_CONNECTION=mysql" `
                     -replace "^#?\s?DB_HOST=.*", "DB_HOST=127.0.0.1" `
                     -replace "^#?\s?DB_PORT=.*", "DB_PORT=3306" `
                     -replace "^#?\s?DB_DATABASE=.*", "DB_DATABASE=inventario_w" `
                     -replace "^#?\s?DB_USERNAME=.*", "DB_USERNAME=root" `
                     -replace "^#?\s?DB_PASSWORD=.*", "DB_PASSWORD=" `
                     | Set-Content ".env"

Write-Host "Generando llaves y corriendo migraciones..." -ForegroundColor Yellow
$php = "..\server\php\php.exe"
& $php artisan key:generate --force
if ($LASTEXITCODE -ne 0) { Write-Error "Fallo la generacion de la llave de APP."; exit }

& $php artisan storage:link
& $php artisan migrate --force
if ($LASTEXITCODE -ne 0) { Write-Error "Fallo la migracion de la base de datos."; exit }

& $php artisan db:seed --force
if ($LASTEXITCODE -ne 0) { Write-Error "Fallo el sembrado de datos (Seeder)."; exit }

& $php artisan optimize:clear

# 10. Iniciar Servidor Web
Write-Host "Iniciando servidor web corporativo..." -ForegroundColor Green
Start-ScheduledTask -TaskName "InventarioW_Web_Server"

# 11. Configurar Git Portable para Actualizaciones
Write-Host "Configurando sistema de actualizaciones (Git Portable)..." -ForegroundColor Yellow
$gitDir = Join-Path $installDir "server\git"
if (-not (Test-Path $gitDir)) { New-Item -ItemType Directory -Path $gitDir -Force | Out-Null }
$gitZip = Join-Path $installDir "deps\git-portable.zip"

if (Test-Path $gitZip) {
    Write-Host "Extrayendo Git..." -ForegroundColor Gray
    Expand-Archive -Path $gitZip -DestinationPath $gitDir -Force
    $gitExe = "$gitDir\cmd\git.exe"
    
    # Configurar Identidad Genetica (Requerido para stash)
    Write-Host "Configurando identidad de Git..." -ForegroundColor Gray
    & $gitExe config --global user.email "update@inventariow.com"
    & $gitExe config --global user.name "Sistema de Actualizacion"
    & $gitExe config --global safe.directory "*"

    # Inicializar Repo si no existe
    Set-Location "$installDir\source"
    if (-not (Test-Path ".git")) {
        Write-Host "Inicializando repositorio Git..." -ForegroundColor Gray
        & $gitExe init
        & $gitExe remote add origin "https://github.com/WilliansHerrera/inventario-w.git"
        & $gitExe fetch origin
        & $gitExe branch -M main
        # Marcar los archivos actuales como 'as-is' para evitar que git quiera borrarlos
        & $gitExe add .
        & $gitExe commit -m "Instalacion inicial"
    }
}

Write-Host "--- INSTALACION COMPLETADA CON EXITO ---" -ForegroundColor Green
Write-Host "Accede en: http://localhost:8000" -ForegroundColor Green
# Read-Host "Presione Enter para finalizar..."
