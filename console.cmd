@echo off
set CALM=
set CALP=
set CALD=%~d0
for %%i in ("%~dp0.") do SET "CALM=%%~fi"
%CALD%
cd %CALM%
color 0f
cls
echo.
echo Executando desde %CALM%
cd console
cmd /k cls

