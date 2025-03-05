const puppeteer = require("puppeteer");
const moment = require("moment-timezone");  // Importiere moment-timezone
const fs = require("fs");
const { exec } = require("child_process");

let browser;
let page;

// Log-Datei definieren
const logFile = "/home/pi/chrome_logfile.log"; // Passe den Pfad zur Log-Datei an

// Funktion, um Logs in die Datei zu schreiben
function logToFile(message) {
    const timestamp = moment().tz("Europe/Berlin").format("YYYY-MM-DD HH:mm:ss");  // MESZ, berücksichtigt Sommerzeit
    fs.appendFileSync(logFile, `${timestamp} - ${message}\n`, "utf8");
}

async function startBrowser() {
    try {
        exec("pgrep chromium", async (error, stdout, stderr) => {
            if (!error && stdout) {
                logToFile("Chromium läuft bereits.");

                // Zusätzliche Prüfung: Schläft Chromium?
                exec("ps -eo comm,state | grep chromium", (psError, psStdout, psStderr) => {
                    if (psError || psStderr) return;

                    const lines = psStdout.trim().split("\n");
                    const allSleeping = lines.every(line => line.includes(" S"));

                    if (allSleeping) {
                        logToFile("Alle Chromium-Instanzen schlafen. Beende und starte neu...");
                        exec("killall chromium", (killError) => {
                            if (killError) {
                                logToFile(`Fehler beim Beenden von Chromium: ${killError.message}`);
                                return;
                            }
                            launchNewChromium(); // Starte eine neue Instanz
                        });
                    }
                });

                return;
            }

            launchNewChromium(); // Falls Chromium nicht läuft, neue Instanz starten
        });

    } catch (error) {
        logToFile(`Fehler beim Starten von Chromium: ${error.message}`);
    }
}

async function launchNewChromium() {
    logToFile("Starte neue Chromium-Instanz...");

    browser = await puppeteer.launch({
        headless: true, // Headless-Modus
        executablePath: "/usr/bin/chromium-browser", // Pfad zum lokalen Chromium eintragen
        args: ["--no-sandbox", "--disable-setuid-sandbox"]
    });

    page = await browser.newPage();
    await page.goto("http://localhost/5d", { waitUntil: "networkidle2" });
    logToFile("Chromium gestartet.");

    // Lässt das Skript weiterlaufen, um setInterval aktiv zu halten
    await new Promise(() => {});
}

async function monitorBrowser()
{
    setInterval(async () => {
        // Prüfe, ob Chromium läuft und ob alle Instanzen schlafen
        exec("ps -eo comm,state | grep chromium", (error, stdout, stderr) => {
            if (error || stderr || !stdout.includes("chromium")) {
                logToFile("Chromium ist abgestürzt oder nicht aktiv, Neustart...");
                restartBrowser();
                return;
            }

            // Prüfe, ob ALLE Chromium-Instanzen im Schlafmodus (S) sind
            const lines = stdout.trim().split("\n");
            const allSleeping = lines.every(line => line.includes(" S"));

            if (allSleeping) {
                logToFile("Alle Chromium-Instanzen schlafen. Neustart...");
                exec("killall chromium", (killError) => {
                    if (killError) {
                        logToFile(`Fehler beim Beenden von Chromium: ${killError.message}`);
                    }
                    restartBrowser();
                });
            }
        });

        // Check if die Seite noch existiert
        if (!page || page.isClosed()) {
            logToFile("Seite ist nicht mehr offen, neu laden...");
            try {
                page = await browser.newPage();
                await page.goto("http://localhost/5d", { waitUntil: "networkidle2" });
                logToFile("Seite wurde neu geladen.");
            } catch (error) {
                logToFile(`Fehler beim Neuladen der Seite: ${error.message}`);
            }
        }
    }, 5000); // Überprüfe alle 5 Sekunden
}

async function restartBrowser()
{
    if (browser)
    {
        try {
            await browser.close(); // Schließe die aktuelle Instanz
            logToFile("Chromium-Instanz wurde geschlossen.");
        } catch (error) {
            logToFile(`Fehler beim Schließen der Instanz: ${error.message}`);
        }
    }

    startBrowser(); // Starte eine neue Instanz
}

// Initial Start
startBrowser();
logToFile("Starte Monitoring alle 5 Sekunden.");
monitorBrowser();