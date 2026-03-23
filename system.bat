@ECHO OFF

cd /d D:\@systemR\local\eperdanamotor


start "Laravel 8028" cmd /k php artisan serve --host=127.0.0.1 --port=8028
start "Laravel 8029" cmd /k php artisan serve --host=127.0.0.1 --port=8029

echo Waiting for servers...

:wait_8028
timeout /t 2 >nul
curl -s http://127.0.0.1:8028 >nul
if errorlevel 1 goto wait_8028

echo Server 8028 ready!

:wait_8029
timeout /t 2 >nul
curl -s http://127.0.0.1:8029 >nul
if errorlevel 1 goto wait_8029

echo Server 8029 ready!

echo All servers ready!

start "" "C:\Program Files\Mozilla Firefox\firefox.exe" http://127.0.0.1:8028/admin/administration

exit