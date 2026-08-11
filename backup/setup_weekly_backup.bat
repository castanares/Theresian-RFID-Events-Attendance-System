@echo off
:: ============================================================
:: setup_weekly_backup.bat
:: Run this ONCE as Administrator to register a Windows
:: Task Scheduler job that runs auto_backup.php every Sunday
:: at 2:00 AM automatically.
:: ============================================================

setlocal

:: ── Configuration ────────────────────────────────────────────
set TASK_NAME=STC_WeeklyAutoBackup
set PHP_EXE=C:\xampp\php\php.exe
set SCRIPT_PATH=C:\xampp\htdocs\Theresian-RFID-Events-Attendance-System\backup\auto_backup.php
set RUN_DAY=SUN
set RUN_TIME=02:00

:: ── Check for admin rights ────────────────────────────────────
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Please run this script as Administrator.
    pause
    exit /b 1
)

:: ── Delete existing task if present ──────────────────────────
schtasks /delete /tn "%TASK_NAME%" /f >nul 2>&1

:: ── Register the weekly task ──────────────────────────────────
schtasks /create ^
  /tn "%TASK_NAME%" ^
  /tr "\"%PHP_EXE%\" \"%SCRIPT_PATH%\"" ^
  /sc WEEKLY ^
  /d %RUN_DAY% ^
  /st %RUN_TIME% ^
  /rl HIGHEST ^
  /f

if %errorLevel% equ 0 (
    echo.
    echo [OK] Task "%TASK_NAME%" created successfully.
    echo      Runs every %RUN_DAY% at %RUN_TIME% using:
    echo        PHP : %PHP_EXE%
    echo        Script: %SCRIPT_PATH%
    echo.
    echo      Backups will be saved to:
    echo        C:\xampp\htdocs\Theresian-RFID-Events-Attendance-System\backup\
    echo.
    echo      To run it NOW for testing:
    echo        schtasks /run /tn "%TASK_NAME%"
) else (
    echo.
    echo [ERROR] Failed to create task. Check paths above and try again.
)

pause
endlocal
