@echo off
echo ====================================
echo  Sync ALL WordPress Code & Push Git
echo ====================================

set WP_ROOT=E:\AmThanhGoc\Code\xapp\htdocs\wordpress\wp-content
set DEST=E:\truyenaudio_app\wordpress_site

echo.
echo [1/4] Copying ALL WordPress source files...
if not exist "%DEST%" mkdir "%DEST%"
if not exist "%DEST%\themes" mkdir "%DEST%\themes"
if not exist "%DEST%\plugins" mkdir "%DEST%\plugins"

xcopy "%WP_ROOT%\themes\truyenaudio" "%DEST%\themes\truyenaudio\" /Y /E /I /Q
xcopy "%WP_ROOT%\plugins\truyenaudio-core" "%DEST%\plugins\truyenaudio-core\" /Y /E /I /Q
echo    Themes + Plugins copied!

echo.
echo [2/4] Git add...
cd /d E:\truyenaudio_app
git add .

echo.
echo [3/4] Git commit -m "Update %date% %time%"
git commit -m "Update WordPress %date% %time%"

echo.
echo [4/4] Git push...
git push

echo.
echo ====================================
echo  Done! CodeMagic will auto build.
echo ====================================
pause
