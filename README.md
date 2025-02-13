WebApp MeshDash-SQL für MeshCom 4

Installation
----------------
Unter Windows einfach in einen http Ordner packen.  
Getestet wurde das unter Windows 10 mit Apache2.

Unter Linux muss zwingend das Install-Skript genutzt werden,  
Es gibt hier sonst Rechte-Probleme, wenn man die Dateien  
manuell kopiert.

Besonderheiten:
------------------
Der UDP Port 1179 darf nicht durch einen anderen Prozess blockiert sein.  
Funktioniert ab PHP V 7.4.x >

Unter Windows:  
SQLite3 muss als Modul in der PHP.ini aktiviert sein.  
Auskommentieren:  
extension=php_pdo_sqlite.dll  
extension=php_pdo_sqlite.dll

Unter Linux bitte die install.sh ausführen.  
Hier werden alle nötigen Dienste/Module für PHP installiert.

Genutzte PHP-Module:
- XML
- ZIP
- Sqlite3


Ziel Ip Lora-Gerät:
--------------------
Die Ip-Adresse des Lora-Gerätes wird  
bei der Neuinstallation abgefragt und in der Sqlite3-DB gespeichert
Erst dann ist auch das Senden von Nachrichten möglich.

Update via Weboberfläche:
-----------------------
Updates können bequem über die Weboberfläche  
ausgeführt werden.

TODO
- Filterfunktionen wie Gruppen-Id oder nach Call
- Tabs pro GruppenId mit Nachrichten der Gruppe