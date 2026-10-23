@echo off
REM ================================================================
REM  Campus Nexus — Local Setup Script for Laragon
REM  Run this AFTER Laragon is installed and "Start All" is clicked.
REM ================================================================

setlocal EnableDelayedExpansion
set "PROJECT_DIR=%~dp0"
set "MYSQL=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe"

REM ── Find mysql.exe (handle different Laragon MySQL versions) ────
if not exist "%MYSQL%" (
    for /d %%D in ("C:\laragon\bin\mysql\mysql-*") do (
        if exist "%%D\bin\mysql.exe" set "MYSQL=%%D\bin\mysql.exe"
    )
)

if not exist "%MYSQL%" (
    echo [ERROR] Could not find mysql.exe in C:\laragon\bin\mysql\
    echo         Make sure Laragon is installed and MySQL is running.
    pause & exit /b 1
)

echo.
echo  ╔══════════════════════════════════════════════════╗
echo  ║       Campus Nexus — Local DB Setup              ║
echo  ╚══════════════════════════════════════════════════╝
echo.
echo  MySQL found: %MYSQL%
echo.

REM ── Import the database schema and seed data ────────────────────
echo  [1/2] Creating database and importing schema...
"%MYSQL%" -u root --password="" < "%PROJECT_DIR%database\campus_nexus.sql"

if %ERRORLEVEL% neq 0 (
    echo  [ERROR] Database import failed. Is Laragon running?
    echo          Click "Start All" in Laragon, then run this again.
    pause & exit /b 1
)
echo  [OK]  Database imported successfully.
echo.

REM ── Verify the database was created ─────────────────────────────
echo  [2/2] Verifying tables...
"%MYSQL%" -u root --password="" -e "USE campus_nexus; SHOW TABLES;" 2>nul
echo.
echo  ════════════════════════════════════════════════════
echo  SUCCESS! Campus Nexus database is ready.
echo.
echo  Open your browser and visit:
echo    http://campus-nexus.test/public/index.php
echo  or (if .test domains are not set up):
echo    http://localhost/campus-nexus/public/index.php
echo.
echo  Default MySQL credentials (local only):
echo    Host:     localhost
echo    User:     root
echo    Password: (empty)
echo    Database: campus_nexus
echo  ════════════════════════════════════════════════════
echo.
pause
