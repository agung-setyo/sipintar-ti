# PowerShell lint script: runs php -l over all PHP files
Get-ChildItem -Recurse -Include *.php | ForEach-Object {
    Write-Output "Checking $_"
    & php -l $_.FullName
    if ($LASTEXITCODE -ne 0) { Write-Error "Syntax error in $_"; exit $LASTEXITCODE }
}
Write-Output "PHP lint passed."