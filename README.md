# WebApp MeshDash-SQL für MeshCom 4

Installation
----------------
Unter Windows einfach in einen http Ordner packen.  
Weiterhin die u.a. **_php.ini_** anpassen für die Module.  
Getestet wurde das unter Windows 10 mit Apache2.

Unter Linux muss zwingend das Install-Skript genutzt werden,  
Es gibt hier sonst Rechte-Probleme, wenn man die Dateien  
manuell kopiert.  
Es fehlen u.U. auch noch gewisse Programme die benötigt werden.

Besonderheiten:
------------------
Der UDP Port 1179 darf nicht durch einen anderen Prozess blockiert sein.  
Funktioniert ab PHP V 7.4.x >

<ins>**Unter Windows:**</ins>  
hier müssen einige Module in der PHP.ini aktiviert sein.  
extension=***php_pdo_sqlite.dll***  
extension=***php_sqlite3.dll***  
extension=***php_zip.dll***

<ins>**Unter Linux:**</ins>  
Hier bitte die install.sh einmalig ausführen.  
Hier werden alle nötigen Dienste/Module für PHP installiert.

Genutzte PHP-Module:
- XML
- ZIP
- Sqlite3
- unzip
- lynx


Ziel Ip Lora-Gerät:
--------------------
Die Ip-Adresse des Lora-Gerätes wird  
bei der Neuinstallation abgefragt und in der Sqlite3-DB gespeichert.  
Erst dann ist auch das Senden von Nachrichten möglich.

Update via Weboberfläche:
-----------------------
Updates können bequem über die Weboberfläche  
ausgeführt werden.