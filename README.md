# MeshDash-SQL

## Was ist MeshDash-SQL?

MeshDash_SQL ist eine Webanwendung zur Anzeige und Verwaltung von Nachrichten  
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
![FILES](/docs/update_menu.jpg)
![FILES](/docs/50_md_release_version.jpg)

**Für die Installation sind nur folgende Dateien relevant:**  
Hier mal ein Beispiel:
- meshdash-sql_V1.10.10.zip (Das Hauptprogramm)
- install.sh (Installation-Skript bei Neuinstallation)
- update.sh (Das Update-Skript ist optional, da Webupdate vorhanden)

![FILES](/docs/files.jpg)

## Was steht in der Kurzanleitung.pdf?

Die **Kurzanleitung.pdf** enthält:


- Eine kleine Installations-Anleitung mit den wichtigsten Schritten zur Einrichtung von MeshDash.

- Ein **kurzes User-Manual** mit Screenshots der Weboberfläche und Erklärungen zu den Menüpunkten.

Diese Datei hilft dir, MeshDash schnell zu installieren und zu nutzen.  
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


### Nun auch mit Tab-Benachrichtigungen bei neuen Nachrichten.
![FILES](/docs/60_desktop_tab_alert.jpg)
![FILES](/docs/61_handy_Tab_alert.jpg)

## Noch offene Punkte die u.a. auf der To-do-Liste stehen:
- MHeard konfigurierbar machen für Request-/Acknowledged Ziele.

### Weitere Featureanfragen stehen unter den ISSUES mit dem TAG Erweiterungen


  
  
