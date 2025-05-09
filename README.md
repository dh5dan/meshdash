# MeshDash-SQL

## Was ist MeshDash-SQL?

MeshDash-SQL ist eine Webanwendung zur Anzeige und Verwaltung von Nachrichten  
auf ESP32-basierten Geräten, die mit der Firmware MeshCom arbeiten.  
Die Verarbeitung und Speicherung der Daten erfolgt über Sqlite3 daher auch der Zusatz SQL.

Dieses Projekt basiert ursprünglich auf ein Projekt von DL4QB.  
Mit seiner tatkräftigen Unterstützung wurde daraus dann dieses Projekt geschaffen.  
Dies ist ein Freizeit-Hobbyprojekt, welches die Anwendung und Bedienung von MeshCom  
auf eine webbasierte Plattform bringen soll.

![RELEASE](/docs/front_menu.jpg)

Vornehmlich ist die Applikation für Linux ausgelegt, sie ist aber auch  
unter Windows lauffähig. Hier muss allerdings schon ein funktionierender Webserver  
mit aktiven PHP-Modulen die nötigt sind vorhanden sein.  
Hier läuft die App unter Win10 mit einem Apache und PHP >= 7.4.23.

Einige Funktionen von MeshDash:

- Eigene Filtergruppen anlegen
- Steuerung von Skripten via Keywords
- Remote Mheard-Abfragen
- Soundbenachrichtigungen für eigene Nachrichten
- Update über die Weboberfläche
- Senden von Befehlen an den Lora
- Sensorabfragen mit Schwellwertbenachrichtigung
- Senden von Nachrichten über eine Send-Queue mit festen Intervallen.  
  Dies gewährleistet, das aufeinanderfolgende Nachrichten mit einem  
  eingestellten Zeitabstand gesendet werden, um Sendekollisionen zu vermeiden.
- Byte-Counter der die verbleibende Anzahl von Zeichen pro Nachricht anzeigt.
- Auswahl gängiger Emoji über ein DropDown.
- Zusätzliche Kennzeichnung neuer Nachrichten mit Hintergrundfarbe und Sound.
- Suchfunktion nach Auswahlkriterien über die gesamte Nachrichtendatenbank möglich.

Das Projekt ist mittlerweile aus der **Beta-Phase**, doch es können immer mal noch Fehler auftreten.  
Falls Probleme auftreten, bitte im Issue-Bereich von GitHub melden,  
sofern sie nicht schon dort vorhanden sind.

## Wo finde ich die Releases?

Die aktuellen Versionen von MeshDash sind direkt auf  
GitHub unter **Releases hier auf der rechten Seite** verfügbar.  

### Hier dann einfach "Latest" anklicken!
![RELEASE](/docs/release.jpg)

🔴 **Wichtiger Hinweis für neue Benutzer:**

Die Dateien befinden sich unter **Assets** – bitte darauf klicken, um die Liste auszuklappen!

![ASSETS](/docs/assets.jpg)

### In MeshDash ist nun ein Abruf der aktuellen Release-Version möglich.
#### Der direkte Abruf des Changelogs kann hier nun auch vorgenommen werden.
![FILES](/docs/update_menu.jpg)

#### Hier wird das Changelog in einem Dialog-Fenster angezeigt.
![FILES](/docs/50_md_release_version.jpg)

**Für die Installation sind nur folgende Dateien relevant:**  
Hier mal ein Beispiel:
- meshdash-sql_V1.10.10.zip (Das Hauptprogramm)
- install.sh (Installation-Skript bei Neuinstallation)
- update.sh (Das Update-Skript ist optional, da Webupdate vorhanden)

![FILES](/docs/files.jpg)

## Was steht in der Manual-Mesh-Dash-SQL.pdf?

Die **Kurzanleitung.pdf** wurde nun abgelöst durch eine vollwertige Anleitung.  
Die neue **Manual-Mesh-Dash-SQL.pdf** enthält:


- Eine Installations-Anleitung mit den wichtigsten Schritten zur Einrichtung von MeshDash.
- Ein **detailliertes User-Manual** mit Screenshots der Weboberfläche und Erklärungen zu den Menüpunkten.
- Ein Troubleshooting Guide mit Tipps zur Fehleranalyse von MeshDash.
 
Diese Datei hilft, MeshDash schnell zu installieren und zu nutzen.  
Sie wird in regelmässigen Abständen aktualisiert.

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
Auch können hier einfach Logdateien heruntergeladen werden,  
die weitere hilfreiche Informationen liefern können.

![FILES](/docs/33_debug_info.jpg)
![FILES](/docs/33_debug_info_logs.jpg)

### Jetzt auch mit Byte-Counter Anzeige  
Die Angabe in Byte ist dem UTF-8 Format geschuldet,  
da diese gegenüber einem ASCII-Zeichen mehr als 1 Byte groß sind.  
Die max. Textlänge ist aber derzeit auf 150 Zeichen beschränkt.

![FILES](/docs/34_msg_byte_counter.jpg)

### Einfügen von Emoji
Über ein Drop-Down kann eine kleine Auswahl an gängigen Emoji vorgenommen werden.
![FILES](/docs/32_send_command.jpg)

### Suchfunktion über die gesamte Nachrichtendatenbank nach Auswahlkriterien
#### Über die Lupe öffnet man den Suchdialog mit den Suchkriterien.
![FILES](/docs/search_01.jpg)
#### MIt Klick auf ein Datumsfeld öffnet sich ein Datetime-Dialog.
![FILES](/docs/search_02.jpg)

### Nun auch mit Tab-Benachrichtigungen bei neuen Nachrichten.
Einstellbar sind die Hintergrundfarbe allgemein und Soundbenachrichtigungen pro Tab-Gruppe.
![FILES](/docs/60_desktop_tab_alert.jpg)
![FILES](/docs/61_handy_Tab_alert.jpg)

### Weitere Featureanfragen stehen unter den ISSUES mit dem TAG Erweiterungen


  
  
