# Backup Instructions

Recommended backup steps:

- Database backup (MySQL): use `mysqldump` or the provided `scripts/backup_db.ps1` on Windows.
- Files backup: copy `uploads/`, `assets/`, and config files to secure storage.
- Automate backups with Windows Task Scheduler or cron and rotate backups (keep X days).

Example (Windows PowerShell):

```powershell
.\scripts\backup_db.ps1 -Host localhost -User root -Password secret -Database sipintar_ti -OutPath C:\backups
```

Store backups offsite and test restores periodically.

## Penjadwalan Otomatis

### Windows (Task Scheduler via PowerShell)
Contoh mendaftarkan task yang menjalankan backup setiap hari pukul 02:00:

```powershell
$action = New-ScheduledTaskAction -Execute "PowerShell.exe" -Argument "-NoProfile -WindowStyle Hidden -File \"C:\\path\\to\\project\\scripts\\backup_db.ps1\" -Host localhost -User root -Password secret -Database sipintar_ti -OutPath C:\\backups"
$trigger = New-ScheduledTaskTrigger -Daily -At 2:00AM
Register-ScheduledTask -TaskName "SIPINTAR-TI Daily DB Backup" -Action $action -Trigger $trigger -Description "Daily DB backup"
```

### Linux (cron)
Edit crontab untuk user yang memiliki akses ke `mysqldump`:

```cron
0 2 * * * /usr/bin/mysqldump -h localhost -u root -proot sipintar_ti > /var/backups/sipintar_ti-$(date +\"%Y%m%d\%H%M%S\").sql
```

## Uji Restore
- Untuk memastikan backup dapat dipulihkan, jalankan restore pada lingkungan staging sebelum mengandalkan backup di produksi.

```bash
mysql -u root -p sipintar_ti_restored < /path/to/backup.sql
```

## Rotasi dan Retensi
- Simpan backup minimal 7-30 hari tergantung kebijakan organisasi.
- Kompres file backup (`gzip`) untuk menghemat ruang.

