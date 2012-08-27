@echo off
echo.
echo Starting SVN Stat + PHP Lint
echo ============================
svn stat |findstr /I /R "\.php$ \.phtml$" >lint.txt
for /F "tokens=2 delims= " %%i in (lint.txt) do q:\php53\php.exe -l %%i |findstr /I /B /V "No syntax errors"
del lint.txt
echo.
echo ============================
echo Finished SVN Stat + PHP Lint
