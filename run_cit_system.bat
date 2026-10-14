@echo off
title CIT Department System Server
echo ======================================================================
echo             CIT DEPARTMENT SYSTEM (Flask Dev Server)
echo ======================================================================
echo  Unified Database : sbc_internship_db (localhost:3306)
echo  Server Address   : http://127.0.0.1:5000
echo  Admin Dashboard  : http://127.0.0.1:5000/admin-login
echo  Default Login    : username: admin  ^|  password: admin123
echo ======================================================================
echo.
cd /d "%~dp0cit_department_system"
"%~dp0.venv\Scripts\python.exe" app.py
pause
