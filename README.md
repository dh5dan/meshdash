# MeshDash-SQL

## Was ist MeshDash-SQL?

MeshDash-SQL ist eine webbasierte Client-Anwendung zur Anzeige und Verwaltung von Nachrichten  
auf Geräten mit der Firmware MeshCom.  
Die Datenverarbeitung und -speicherung erfolgt über SQLite3, daher der Zusatz "SQL".

Dieses Projekt basiert ursprünglich auf der Arbeit von DL4QB.  
Mit seiner tatkräftigen Unterstützung wurde daraus dieses Projekt entwickelt.  
Es handelt sich um ein Freizeit-Hobbyprojekt, das die Anwendung und Bedienung von MeshCom  
auf eine webbasierte Plattform bringen soll.

![RELEASE](/docs/front_menu.jpg)

MeshDash-SQL wurde primär für den Einsatz auf einem Raspberry Pi mit Linux entwickelt.  
PHP (mindestens Version 7.4) ist nötig.  
Benötigt werden u.a. die PHP-Module sqlite3, curl, zip und unzip.

### ⚠️ Hinweis
Die Unterstützung für **PHP 7.4** wird in zukünftigen Versionen entfallen.   
Eine Umstellung auf **PHP >= 8.x** ist daher empfohlen.

### Einige Funktionen von MeshDash:

- Echtzeit-Anzeige der Nachrichten
- Speicherung und Archivierung der Daten mittels SQLite3-Datenbank
- Anlage individuelle Filtergruppen
- Ausführung von Skripten via Keywords
- Remote Mheard-Abfragen
- Soundbenachrichtigungen für eigene Nachrichten
- Update über die Weboberfläche inkl. Download
- Senden von Befehlen an den Node
- Sensorabfragen mit Schwellwertbenachrichtigung
- Senden von Nachrichten über eine Send-Queue mit festen Intervallen.  
  Dies gewährleistet, das aufeinanderfolgende Nachrichten mit einem  
  eingestellten Zeitabstand gesendet werden, um Sendekollisionen zu vermeiden.
- Anzeige via Byte-Counter über die verbleibende Anzahl von Zeichen pro Nachricht.
- Auswahl gängiger Emoji über ein DropDown zum Einfügen in die Nachricht.
- Zusätzliche Kennzeichnung neuer Nachrichten mit Hintergrundfarbe und Sound.
- Suchfunktion nach Auswahlkriterien über die gesamte Nachrichtendatenbank möglich.
- Restore-Funktion eines zuvor gesicherten Backups
- Darstellung der Nachrichten im Bubble-Style

Das Projekt ist inzwischen aus der Beta-Phase, kann aber weiterhin Fehler enthalten.  
Wenn Probleme auftreten, bitte im GitHub-Issue-Bereich melden – falls das Problem  
noch nicht dort beschrieben ist.
Es gibt außerdem eine Telegram-Gruppe zu MeshDash.  
Den Link zur Einladung senden wir auf Anfrage gerne zu.

## Wo finde ich die Releases?

Die aktuellen Releases von MeshDash findest du auf der GitHub-Seite unter dem Reiter „Releases“:    
https://github.com/dh5dan/meshdash/releases

Dort stehen jeweils die aktuellen Versionen als ZIP-Dateien zum Download bereit.

### Hier ist "Latest" anzuklicken!
![RELEASE](/docs/release.jpg)

🔴 **Wichtiger Hinweis für neue Benutzer:**

Die Dateien befinden sich unter **Assets** – bitte darauf klicken, um die Liste auszuklappen!

![ASSETS](/docs/assets.jpg)

### In MeshDash ist nun ein Abruf der aktuellen Release-Version möglich.
#### Die letzte Release kann direkt geladen werden, um auf dem neuesten Stand zu bleiben.
#### Der Changelog ist ebenfalls direkt einsehbar.
![FILES](/docs/update_menu.jpg)

#### Hier wird das Changelog in einem Dialog-Fenster angezeigt.
![FILES](/docs/50_md_release_version.jpg)

**Für die Installation sind nur folgende Dateien relevant:**  
Hier mal ein Beispiel:
- meshdash-sql_Vx.xx.xx.zip - Das Hauptprogramm
- install.sh - Installationsskript (bei Neuinstallation)
- update_5d.sh - Update-Skript - (optional, da Webupdate möglich)
- Manual-MeshDas-SQL.pdf - Ausführliches Benutzerhandbuch

![FILES](/docs/files.jpg)

### Restore-Funktion eines zuvor angelegten Backups
MeshDash-SQL bietet die Möglichkeit, zuvor erstellte Backups wiederherzustellen.  
So kannst du bei Problemen oder nach einem Update den vorherigen Zustand einfach zurückspielen.  

Wichtig:   
Die Datensicherheit ist nur gewährleistet, wenn auch die Datenbank mitgesichert wurde.  
Der Restore erfolgt über das Webinterface oder manuell, je nach Bedarf.
![FILES](/docs/restore.jpg)

### Die Ansicht kann nun auch im Bubble-Styl dargestellt werden
Das Layout zeigt Nachrichten nun als Sprechblasen, ähnlich wie bei Messenger-Apps,  
was für eine bessere Übersicht und ein moderneres Design sorgt.
![FILES](/docs/bubble_style_view.jpg)

## Was steht in der Manual-Mesh-Dash-SQL.pdf?

Die frühere Kurzanleitung.pdf wurde durch eine umfassende Anleitung ersetzt: die Manual-Mesh-Dash-SQL.pdf.

Diese enthält:

- Eine Installationsanleitung mit den wichtigsten Schritten zur Einrichtung von MeshDash.
- Ein detailliertes Benutzerhandbuch mit Screenshots der Weboberfläche und Erläuterungen zu den Menüpunkten.
- Einen Troubleshooting-Guide mit Tipps zur Fehleranalyse.

Das Manual unterstützt dabei, MeshDash schnell zu installieren und effektiv zu nutzen.  
Es wird regelmäßig aktualisiert.

## Hier noch ein paar Bilder aus den Menüpunkten:

![FILES](/docs/01_config.jpg)
![FILES](/docs/01_1_send_queue.jpg)
![FILES](/docs/02_alert.jpg)
![FILES](/docs/03_keyword.jpg)
![FILES](/docs/04_update.jpg)
![FILES](/docs/10_groups.jpg)
![FILES](/docs/20_mheard.jpg)
![FILES](/docs/30_send_command.jpg)
![FILES](/docs/31_send_command.jpg)

### Debug-Informationen sofort auf einen Blick
Ideal zur Fehleranalyse als Screenshot.  
Hier können außerdem unkompliziert Logdateien heruntergeladen werden,  
die zusätzliche hilfreiche Informationen liefern.

![FILES](/docs/33_debug_info.jpg)
![FILES](/docs/33_debug_info_logs.jpg)

### Byte-Counter Anzeige  
Die Byte-Anzeige berücksichtigt das UTF-8-Format,  
da UTF-8-Zeichen mehr als 1 Byte belegen können.  
Die maximale Textlänge ist aktuell auf 150 Zeichen begrenzt.

![FILES](/docs/34_msg_byte_counter.jpg)

### Emoji einfügen
Über ein Drop-Down-Menü kann aus einer Auswahl gängiger Emojis gewählt werden.
![FILES](/docs/32_send_command.jpg)

### Suchfunktion über die gesamte Nachrichtendatenbank
Über das Lupen-Symbol wird der Suchdialog geöffnet, in dem verschiedene Suchkriterien ausgewählt werden können.
![FILES](/docs/search_01.jpg)
#### Datums- und Zeit-Dialogfenster.
Ein Klick auf ein Datumsfeld öffnet einen Datums- und Zeit-Dialog zur einfachen Auswahl.
![FILES](/docs/search_02.jpg)

### Tab-Benachrichtigungen bei neuen Nachrichten.
Die Hintergrundfarbe der Tabs sowie Soundbenachrichtigungen lassen sich individuell pro Tab-Gruppe einstellen.
![FILES](/docs/60_desktop_tab_alert.jpg)

### Mobile Ansicht auf einem Smartphone 
![FILES](/docs/61_handy_Tab_alert.jpg)

### Features und Bugs
Alle aktuellen Features und gemeldeten Bugs sind im GitHub-Bereich ISSUES  
dokumentiert und können dort verfolgt werden.


  
  
