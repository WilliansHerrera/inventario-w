; Script de Inno Setup para Inventario-W
; Diseñado para empaquetado Offline (Fat Installer)

[Setup]
AppName=Inventario-W Server
AppVersion=2.2.0
DefaultDirName=C:\Inventario-W
DefaultGroupName=Inventario-W
OutputDir=.
OutputBaseFilename=Inventario-W-V2.2.0
Compression=lzma
; Imagenes de marca (Inno Setup solo soporta .BMP, comentar estos si dan error de Bitmap)
; WizardImageFile=dist\pos_inventory_icon_1775924858045.png
; WizardSmallImageFile=dist\pos_inventory_icon_1775924858045.png
; SetupIconFile=inventario.ico
PrivilegesRequired=admin

[Languages]
Name: "spanish"; MessagesFile: "compiler:Languages\Spanish.isl"

[Files]
; Copiar todo lo preparado en la carpeta dist
Source: "dist\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{group}\Inventario-W Admin"; Filename: "http://localhost:8000"; IconFilename: "{app}\pos_inventory_icon_1775924858045.png"
Name: "{commondesktop}\Inventario-W Admin"; Filename: "http://localhost:8000"; IconFilename: "{app}\pos_inventory_icon_1775924858045.png"
Name: "{commondesktop}\Control Panel Inventario-W"; Filename: "{app}\Control-Panel.bat"; IconFilename: "{app}\pos_inventory_icon_1775924858045.png"

[Run]
; Ejecutar la configuracion interna despues de extraer (SIN runhidden para ver errores)
Filename: "{sys}\WindowsPowerShell\v1.0\powershell.exe"; \
    Parameters: "-ExecutionPolicy Bypass -File ""{app}\internal_setup.ps1"" ""{app}"""; \
    Flags: waituntilterminated; \
    StatusMsg: "Configurando base de datos y levantando servidor en puerto 8000..."

[UninstallRun]
Filename: "{sys}\WindowsPowerShell\v1.0\powershell.exe"; \
    Parameters: "-Command ""Unregister-ScheduledTask -TaskName 'InventarioW_Web_Server' -Confirm:$false; Unregister-ScheduledTask -TaskName 'InventarioW_DB_Server' -Confirm:$false"""; \
    Flags: runhidden
