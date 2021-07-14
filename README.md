> [english version](README.en.md)

# D³ Data Wizard für OXID eShop

Das Modul `DataWizard` bietet ein Framework zur einfachen Integration von Exporten über den Adminbereich des OXID Shops.

Die Exporte werden über Datenbankabfragen oder fertige Datenlisten definiert. Es stehen verschiedene Exportformate zur Verfügung. Die Generierung ist jederzeit und immer wiederkehrend möglich (im Rahmen der Systemgrenzen). Diese werden im Browser als Download angeboten.

Alle Exporte sind für eine bessere Übersichtlichkeit in Gruppen zusammengefasst.

Im Lieferumfang sind Beispielexporte enthalten. Diese sollen als Implementierungsreferenz für individuelle Exporte dienen. Die Anzeige der Beispielexporte kann in den Modulgrundeinstellungen deaktiviert werden.

## Schnellinstallation

Auf der Konsole im Shoproot (oberhalb von source und vendor) folgenden Befehl ausführen:

```bash
php composer require d3/datawizard
``` 

Aktivieren Sie das Modul im Shopadmin unter "Erweiterungen -> Module".

## Erweiterbarkeit

Das Modul stellt das technische Grundgerüst der Exporte dar und erhebt keinen Anspruch auf Vollständigkeit. Um den Umfang an die individuellen Anforderungen anzupassen, sind folgende Erweiterungen vorbereitet:

- Exporte hinzufügen
- Verwendung bestehender und neuer Gruppen
- Exportformate ergänzen

Unabhängig dessen stehen alle Erweiterungsmöglichkeiten zur Verfügung, die der OXID Shop für Module bereitstellt.

### Exporte hinzufügen

#### Export definieren

Jeder Export wird in einer separaten Klasse definiert. Diese Exportklasse muss die Klasse `D3\DataWizard\Application\Model\ExportBase` erweitern. Darin sind alle nötigen Funktionen vordefiniert. Folgende Methoden stehen hierbei zur Verfügung:

##### verpflichtende Methodenaufrufe:
- getTitle() - definiert den Titel im Adminbereich und die Basis des späteren Exportdateinamens
- getQuery() - enthält den Abfragequery als prepared statement, der die zu exportierenden Daten definiert

##### optionale Methodenaufrufe:
- getDescription() - enthält eine kurze zusätzliche Beschreibung des Exports, dieser wird im Adminbereich gezeigt
- getButtonText() - definiert den Text des Absenden-Buttons im Adminbereich
- getExportFilenameBase() - definiert die Basis des späteren Exportdateinamens
- executeQuery() - liefert die zusammengestellten Exportdaten
- weitere Methoden, deren Anpassung jedoch zum geänderten Modulverhalten führen können und daher nur mit Vorsicht zu verändern sind

#### Exporte registrieren

Um den erstellten Export auch im Modul verwenden zu können, muss dieser noch registriert werden. Hierzu gibt des die Klasse `D3\DataWizard\Application\Model\Configuration`. Diese ist mit den Möglichkeiten des OXID Shops zu überladen und die darin enthaltene Methode `configure()` zu ergänzen. Für die Registrierung eines Exportes steht Ihnen dazu folgender Aufruf zur Verfügung:

```
$this->registerExport(self::GROUP_CATEGORY, oxNew(myCustomExport::class));
```

Der erste Parameter enthält den Sprachident für die Exportgruppe. In Konstanten der configuration-Klasse sind schon typische Idents vorbereitet. Als 2. Parameter wird die Instanz der Exportklasse erwartet.

## Changelog

Siehe [CHANGELOG](CHANGELOG.md) für weitere Informationen.

## Lizenz dieser Software (d3/datawizard)
(Stand: 06.05.2021)

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

Diese Software wird unter der GNU GENERAL PUBLIC LICENSE Version 3 vertrieben.
```

Die vollständigen Copyright- und Lizenzinformationen entnehmen Sie bitte der [LICENSE](LICENSE.md)-Datei, die mit diesem Quellcode verteilt wurde.

## weitere Lizenzen und Nutzungsbedingungen

### Hintergrundgradienten in src/Application/views/admin/tpl/ Templates
(https://www.gradientmagic.com/licensing - Stand: 07.05.2021)

```
Image courtesy of gradientmagic.com

Free Gradients

Gradients available on the site are free to use on personal and commercial projects, with attribution.
```

-------------------------------------------------------------------------------

Die folgenden Softwarepakete sind nicht Teil dieses Moduls. Sie werden diese jedoch zur Verwendung benötigt. Die verlinkten Pakete stehen unter den folgenden Lizenzen:

### league/csv [MIT]
(https://github.com/thephpleague/csv - Stand: 06.05.2021)

```
Copyright (c) 2013-2017 ignace nyamagana butera

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```

### mathieuviossat/arraytotexttable [MIT]
(https://github.com/viossat/arraytotexttable - Stand: 06.05.2021)

```
Copyright (c) 2015 Mathieu Viossat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
```
