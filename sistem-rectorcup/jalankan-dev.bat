@echo off
echo ==========================================
echo STARTING LARAVEL DEV ENVIRONMENT
echo ==========================================
echo 1. Opening Serve...
start powershell -NoExit -Command "cd sistem-rectorcup; echo 'Running: php artisan serve'; php artisan serve"
echo 2. Opening Queue...
start powershell -NoExit -Command "cd sistem-rectorcup; echo 'Running: php artisan queue:work'; php artisan queue:work"
echo 3. Opening Reverb...
start powershell -NoExit -Command "cd sistem-rectorcup; echo 'Running: php artisan reverb:start'; php artisan reverb:start"
echo ==========================================
echo Done! 3 Windows opened.
pause