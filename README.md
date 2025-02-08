WebApp MeshDash für Meshcom 4
=
Aktuelle Version 1.08.18


Installation
----------------
Einfach in einen http Ordner packen.  
Funktioniert mit Apache2/Lighttpd
sowohl auf Windows wie auch auf Linux.

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
wird in der Datei **dbinc/param.php**
angegeben werden.  
Erst dann ist auch das Senden von Nachrichten möglich.

TODO
- Filterfunktionen wie Gruppen-Id oder nach Call
- Tabs pro GruppenId mit Nachrichten der Gruppe
- Mh-Heard Liste lesen
- Online Update Funktion