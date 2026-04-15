@echo off
setlocal
set APP_DIR=%~dp0

:: Verificar permisos de Administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ======================================================
    echo ERROR: Se requieren permisos de ADMINISTRADOR.
    echo Por favor, haz clic derecho y "Ejecutar como administrador".
    echo ======================================================
    pause
    exit /b
)

:menu
cls
echo ==========================================
echo    PANEL DE CONTROL - INVENTARIO-W
echo ==========================================
echo.
echo [1] Iniciar Servidor (Puerto 8000)
echo [2] Detener Servidor
echo [3] Ver Estado del Sistema
echo [4] Salir
echo.
set /p opt="Seleccione una opcion: "

if "%opt%"=="1" goto start
if "%opt%"=="2" goto stop
if "%opt%"=="3" goto status
if "%opt%"=="4" exit
goto menu

:start
echo Iniciando MySQL...
schtasks /run /tn "InventarioW_DB_Server" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] No se pudo iniciar el motor de DB. Reinstale el sistema.
    pause
    goto menu
)
timeout /t 5 /nobreak >nul
echo Iniciando Servidor PHP...
schtasks /run /tn "InventarioW_Web_Server" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] No se pudo iniciar el servidor Web. Reinstale el sistema.
    pause
    goto menu
)
echo.
echo Servidor iniciado exitosamente.
echo Acceda en: http://localhost:8000
pause
goto menu

:stop
echo Deteniendo servicios (Programador de Tareas)...
schtasks /end /tn "InventarioW_Web_Server" >nul 2>&1
schtasks /end /tn "InventarioW_DB_Server" >nul 2>&1
echo Limpiando procesos residuales...
taskkill /f /im php.exe /t >nul 2>&1
taskkill /f /im mysqld.exe /t >nul 2>&1
echo Sistema detenido.
pause
goto menu

:status
tasklist /fi "imagename eq php.exe" | find /i "php.exe" >nul
if %errorlevel%==0 (echo [OK] PHP esta corriendo.) else (echo [OFF] PHP no esta corriendo.)
tasklist /fi "imagename eq mysqld.exe" | find /i "mysqld.exe" >nul
if %errorlevel%==0 (echo [OK] MySQL esta corriendo.) else (echo [OFF] MySQL no esta corriendo.)
pause
goto menu

