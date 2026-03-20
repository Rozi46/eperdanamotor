@ECHO OFF

@REM start cmd /k "cd /d D:\@systemR\local\eperdanamotor && php artisan serve --port=8029"

start cmd /k "cd /d D:\@systemR\local\eperdanamotor && php artisan serve --port=8028"

timeout /t 5 >nul

start firefox http://localhost:8028/admin/administration
