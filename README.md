# Start
```
git clone https://github.com/hajertamas/bluespotapp
cd backend
composer install
```
A backend/config.php-ban kell beállítani az adatbázist, app url-t, devmode, stb..

Adatbázis migráció, telepítés kezelése cli-ből a backend/migrate.php
```
\backend>php migrate.php --install
```
CLI parancsok a migrate.php-n:
```
\backend>php migrate.php --help
```
Ezután a frontend/src/environments/environment.ts mappában be kell állítani a backend url-jét (pl.: http://localhost/mappa-neve/backend/api.php)

Innentől működik az app:
```
cd ../frontend
npm install
ng serve --o
```

Teszt adatbázis import (test_import mappát tartalmazza a repo):
```
\backend>php migrate.php --uninstall --install --import test_import
```
Ez a script újratelepíti az adatbázist, majd beletölti a teszt adatokat, ezt látni is kell egyből az appon:
Belépés a teszt adatokkal:
email: test@user.com
jelszó: admin1

Exportálni az --export commanddal lehet:
```
\backend>php migrate.php --export
```
Ez a backend\bluespot-app-export-{currentTimestamp} mappába exportálja az adatbázist, ezt átlehet nevezni és használni importra (a \backend mappában kell lennie):
```
\backend>php migrate.php --import new_export_name
```
