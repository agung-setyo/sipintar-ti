# Simple MySQL dump backup script for Windows
# Usage: .\backup_db.ps1 -Host localhost -User root -Password secret -Database sipintar_ti -OutPath C:\backups
param(
    [string]$Host = 'localhost',
    [string]$User = 'root',
    [string]$Password = 'root',
    [string]$Database = 'sipintar_ti',
    [string]$OutPath = '.'
)

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$outFile = Join-Path $OutPath "$($Database)-backup-$timestamp.sql"

$mysqldump = "mysqldump.exe"

Write-Output "Dumping database $Database to $outFile"
& $mysqldump -h $Host -u $User -p$Password $Database > $outFile
if ($LASTEXITCODE -eq 0) {
    Write-Output "Backup successful: $outFile"
} else {
    Write-Error "Backup failed with exit code $LASTEXITCODE"
}
