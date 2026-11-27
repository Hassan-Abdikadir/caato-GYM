@echo off
title CAATO FITNESS GYM - Local Server (MySQL 8.0 + PHP 8.3)
color 0A
echo ============================================================
echo   CAATO FITNESS GYM - CAATO OS v4.0 (2026 ENTERPRISE)
echo ============================================================
echo.

set "MYSQL_DIR=%LOCALAPPDATA%\caato-mysql"
set "PHP_EXE=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL 8.0 Database Server is already running on 127.0.0.1:3306
) else (
    echo [*] Starting MySQL 8.0 Database Server...
    start "" /B "%MYSQL_DIR%\bin\mysqld.exe" --basedir="%MYSQL_DIR%" --datadir="%MYSQL_DIR%\data" --port=3306
    timeout /t 3 /nobreak >nul
    echo [OK] MySQL 8.0 Database Server started!
)

echo [*] Opening http://localhost:8080 in your default browser...
start "" "http://localhost:8080"

echo [*] Starting PHP 8.3 Web Server on http://localhost:8080 ...
echo     (Keep this window open while using CAATO GYM)
echo.
"%PHP_EXE%" -S localhost:8080 -t "%~dp0"
