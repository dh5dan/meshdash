WebApp MeshDash für Meshcom 4

Installation
----------------
Unter Windows einfach in einen http Ordner packen.  
Getestet wurde das unter Windows 10 mit Apache2.

Unter Linux muss zwingend das Install-Skript genutzt werden,
bzw. bei Updates das Update-Skript. 
Es gibt hier sonst Rechte-Probleme, wenn man die Dateien
manuell kopiert.

Besonderheiten:
------------------
Der UDP Port 1179 darf nicht durch einen anderen Prozess blockiert sein.  
Funktioniert ab PHP V 7.4.x >

SQLite3 muss als Modul in der PHP.ini aktiviert sein.  
Auskommentieren:  
extension=pdo_sqlite  
extension=sqlite3

Ziel Ip Lora-Gerät:
--------------------
Die Ip-Adresse des Lora-Gerätes
Bei der Neuinstallation abgefragt und in der Sqlite3-DB gespeichert
Erst dann ist auch das Senden von Nachrichten möglich.

TODO
- Filterfunktionen wie Gruppen-Id oder nach Call
- Tabs pro GruppenId mit Nachrichten der Gruppe
- Remote Mh-Heard Liste abrufen
- Online Update Funktion