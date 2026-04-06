@echo off
REM Quick Start Script for Participant Management System with Real-Time Broadcasting

echo.
echo ========================================
echo Participant Management System Startup
echo ========================================
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo ERROR: artisan file not found. Make sure you're in the Laravel root directory.
    pause
    exit /b 1
)

REM Check if composer dependencies are installed
if not exist "vendor" (
    echo Installing Composer dependencies...
    call composer install
)

REM Check if node dependencies are installed
if not exist "node_modules" (
    echo Installing NPM dependencies...
    call npm install
)

echo.
echo What would you like to do?
echo 1. Run dev server + Reverb (RECOMMENDED)
echo 2. Run dev server only
echo 3. Run Reverb only
echo 4. Build for production
echo 5. Run tests
echo 6. Clear cache
echo.
set /p choice="Enter your choice (1-6): "

if "%choice%"=="1" (
    echo.
    echo Starting Vite dev server (http://localhost:5173)...
    echo In another terminal window, the Reverb server will start (ws://localhost:8080)
    echo.
    echo Press Ctrl+C to stop either process.
    echo.
    start cmd /k "npm run dev"
    echo.
    echo Waiting 3 seconds for Vite to start...
    timeout /t 3 /nobreak
    echo.
    echo Starting Reverb WebSocket server on port 8080...
    echo.
    call php artisan reverb:start
) else if "%choice%"=="2" (
    echo.
    echo Starting Vite dev server only (http://localhost:5173)...
    echo.
    call npm run dev
) else if "%choice%"=="3" (
    echo.
    echo Starting Reverb WebSocket server on port 8080...
    echo Open http://localhost in another terminal window.
    echo.
    call php artisan reverb:start
) else if "%choice%"=="4" (
    echo.
    echo Building for production...
    echo.
    call npm run build
    echo.
    echo Build complete!
) else if "%choice%"=="5" (
    echo.
    echo Running tests...
    echo.
    call php artisan test
) else if "%choice%"=="6" (
    echo.
    echo Clearing cache...
    echo.
    call php artisan cache:clear
    call php artisan config:clear
    call php artisan view:clear
    call php artisan route:clear
    echo Cache cleared!
) else (
    echo Invalid choice. Exiting.
    pause
    exit /b 1
)

pause
