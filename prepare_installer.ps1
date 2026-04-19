<#
.SYNOPSIS
    Script de Preparación para el Instalador EXE de Inventario-W.
    Reúne el código, descarga PHP y MySQL, y organiza la carpeta 'dist'.
#>

$distPath = "dist"
$depsPath = "$distPath\deps"
$sourcePath = "$distPath\source"

# 1. Crear estructura de carpetas (Preservando 'deps')
if (-not (Test-Path $distPath)) { New-Item -ItemType Directory -Path $distPath -Force | Out-Null }
if (-not (Test-Path $depsPath)) { New-Item -ItemType Directory -Path $depsPath -Force | Out-Null }

# Limpiar solo la carpeta de codigo fuente para evitar basura
if (Test-Path $sourcePath) { Remove-Item $sourcePath -Recurse -Force | Out-Null }
New-Item -ItemType Directory -Path $sourcePath -Force | Out-Null

# 2. Descargar Motores (PHP y MySQL)
$phpZip = "$depsPath\php-8.2.zip"
if (-not (Test-Path $phpZip)) {
    Write-Host "Descargando PHP 8.2..." -ForegroundColor Yellow
    $phpUrl = "https://windows.php.net/downloads/releases/archives/php-8.2.28-nts-Win32-vs16-x64.zip"
    Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip -UserAgent "Mozilla/5.0"
} else {
    Write-Host "PHP ya estÃ¡ en la carpeta deps, saltando descarga..." -ForegroundColor Green
}

$mysqlZip = "$depsPath\mysql-8.0.zip"
if (-not (Test-Path $mysqlZip)) {
    Write-Host "Descargando MySQL 8.0 (Intentando Espejos)..." -ForegroundColor Yellow
    $mirrors = @(
        "https://mirrors.sjtug.sjtu.edu.cn/mysql/Downloads/MySQL-8.0/mysql-8.0.35-winx64.zip",
        "https://ftp.heanet.ie/mirrors/mysql/Downloads/MySQL-8.0/mysql-8.0.35-winx64.zip"
    )
    
    foreach ($url in $mirrors) {
        Write-Host "Probando espejo: $url" -ForegroundColor Gray
        try {
            & curl.exe -L -A "Mozilla/5.0" -m 600 -o $mysqlZip "$url"
            if (Test-Path $mysqlZip) {
                Write-Host "Descarga de MySQL exitosa desde: $url" -ForegroundColor Green
                break
            }
        } catch {
            Write-Host "Fallo en este espejo, intentando el siguiente..." -ForegroundColor Red
        }
    }
} else {
    Write-Host "MySQL ya está en la carpeta deps, saltando descarga..." -ForegroundColor Green
}

$gitZip = "$depsPath\git-portable.zip"
if (-not (Test-Path $gitZip)) {
    Write-Host "Descargando MinGit (Git Portable) 64-bit..." -ForegroundColor Yellow
    # URL de MinGit (Version ligera para automatizacion)
    $gitUrl = "https://github.com/git-for-windows/git/releases/download/v2.44.0.windows.1/MinGit-2.44.0-64-bit.zip"
    try {
        Invoke-WebRequest -Uri $gitUrl -OutFile $gitZip -UserAgent "Mozilla/5.0"
        Write-Host "Descarga de Git exitosa." -ForegroundColor Green
    } catch {
        Write-Warning "Fallo la descarga de Git desde GitHub. El sistema de actualizaciones podria no funcionar correctamente."
    }
} else {
    Write-Host "Git ya está en la carpeta deps, saltando descarga..." -ForegroundColor Green
}

# 3. Copiar Código Fuente (Excluyendo basura y ejecutables previos)
Write-Host "Copiando codigo fuente de forma selectiva..." -ForegroundColor Yellow
# Usamos robocopy para exclusiones recursivas robustas
$roboArgs = @(
    ".", 
    $sourcePath, 
    "/S", "/E", "/NFL", "/NDL", "/NJH", "/NJS", "/nc", "/ns", "/np",
    "/XD", ".git", ".github", "node_modules", "dist", "target", "storage", "vendor",
    "/XF", "*.exe", "*.zip", ".env", "*.iss", "*.ps1", "Control-Panel.bat"
)
& robocopy.exe @roboArgs

# Copiar la carpeta 'vendor' (laravel lo necesita)
& robocopy.exe "vendor" "$sourcePath\vendor" /S /E /NFL /NDL /NJH /NJS /nc /ns /np

# Copiar activos visuales de MoonShine (CRITICO para que cargue el UI)
if (Test-Path "public\vendor") {
    & robocopy.exe "public\vendor" "$sourcePath\public\vendor" /S /E /NFL /NDL /NJH /NJS /nc /ns /np
}

# Crear carpetas esenciales vacias
New-Item -ItemType Directory -Path "$sourcePath\storage\logs" -Force | Out-Null
New-Item -ItemType Directory -Path "$sourcePath\storage\framework\views" -Force | Out-Null
New-Item -ItemType Directory -Path "$sourcePath\storage\framework\sessions" -Force | Out-Null
New-Item -ItemType Directory -Path "$sourcePath\storage\framework\cache" -Force | Out-Null
New-Item -ItemType Directory -Path "$sourcePath\public\downloads" -Force | Out-Null

# Copiar el instalador del POS a la carpeta de descargas del servidor
$posMsi = "storage/app/public/pos/POS-Setup.msi"
if (Test-Path $posMsi) {
    Write-Host "Copiando terminal POS MSI a la carpeta de descargas..." -ForegroundColor Gray
    Copy-Item $posMsi -Destination "$sourcePath\public\downloads\POS-Setup.msi" -Force
} else {
    Write-Warning "No se encontro el instalador MSI de la caja POS. Se saltara la inclusion."
}

$posExe = "POS-Windows\src-tauri\target\release\POS-Inventario-W.exe"
if (Test-Path $posExe) {
    Write-Host "Copiando executable POS legacy..." -ForegroundColor Gray
    Copy-Item $posExe -Destination "$sourcePath\public\downloads\POS-Scanner-Setup.exe" -Force
}


# 4. Copiar Scripts de Instalación Interna
Copy-Item "internal_setup.ps1" -Destination $distPath
Copy-Item "Control-Panel.bat" -Destination $distPath
Copy-Item "pos_inventory_icon_1775924858045.png" -Destination $distPath
Copy-Item "MANUAL_USUARIO.md" -Destination $distPath

Write-Host "--- Preparación Lista ---" -ForegroundColor Green
Write-Host "Ahora puedes abrir 'inventario_installer.iss' en Inno Setup y pulsar Compilar."
