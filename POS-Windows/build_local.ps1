$repoRoot = $PSScriptRoot
$posDir = $repoRoot # Assuming the script is in POS-Windows

# Update PATH for this session
$extraPaths = @(
    "C:\Users\Willians1\.cargo\bin",
    "C:\Program Files\WiX Toolset v7.0\bin",
    "C:\Program Files (x86)\WiX Toolset v3.11\bin"
)

foreach ($path in $extraPaths) {
    if (Test-Path $path) {
        if ($env:Path -notlike "*$path*") {
            $env:Path = "$path;$env:Path"
        }
    }
}

Write-Host "--- Verifying Environment ---" -ForegroundColor Cyan
rustc --version
cargo --version
node --version
Write-Host "npm version: " -NoNewline; npm.cmd --version

# Check if wix or candle/light are available
if (Get-Command candle -ErrorAction SilentlyContinue) {
    Write-Host "WiX v3 found: $(Get-Command candle | Select-Object -ExpandProperty Definition)" -ForegroundColor Green
} elseif (Get-Command wix -ErrorAction SilentlyContinue) {
    Write-Host "WiX v4+ found: $(Get-Command wix | Select-Object -ExpandProperty Definition)" -ForegroundColor Yellow
} else {
    Write-Warning "WiX not found in PATH. Build might fail if Tauri can't find it."
}

Write-Host "--- Starting Tauri Build ---" -ForegroundColor Cyan
Set-Location $posDir

# Ensure dependencies are installed
Write-Host "Checking/Installing dependencies..."
npm.cmd install

# Run Tauri Build
npm.cmd run tauri -- build
