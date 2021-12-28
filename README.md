[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# D³ Data Wizard für OXID eShop

Das Modul `DataWizard` bietet ein Framework zur einfachen Integration von Exporten und ausführbaren Tasks über den Adminbereich des OXID Shops.

Die Exporte werden über Datenbankabfragen oder fertige Datenlisten definiert. Es stehen verschiedene Exportformate zur Verfügung. Die Generierung ist jederzeit und immer wiederkehrend möglich (im Rahmen der Systemgrenzen). Diese werden im Browser als Download angeboten.

Alle Exporte bzw. Tasks sind für eine bessere Übersichtlichkeit in Gruppen zusammengefasst.

Im Paket `d3/datawizardtasks` sind Beispielexporte enthalten. Diese sollen als Implementierungsreferenz für individuelle Exporte dienen.

![Adminbereich](assets/administration_exports.jpg "Adminbereich")

## Inhaltsverzeichnis

- [Installation](#installation)
- [Verwendung](#verwendung)
- [Erweiterbarkeit](#erweiterbarkeit)
    - [Erweiterungspakete](#erweiterungspakete)
- [Changelog](#changelog)
- [Beitragen](#beitragen)
- [Lizenz](#lizenz)
- [weitere Lizenzen und Nutzungsbedingungen](#weitere-lizenzen-und-nutzungsbedingungen)

## Installation

Dieses Paket erfordert einen mit Composer installierten OXID eShop in einer in der [composer.json](composer.json) definierten Version.

Öffnen Sie eine Kommandozeile und navigieren Sie zum Stammverzeichnis des Shops (Elternverzeichnis von source und vendor). Führen Sie den folgenden Befehl aus. Passen Sie die Pfadangaben an Ihre Installationsumgebung an.


```bash
php composer require d3/datawizard:^2.0
``` 

Aktivieren Sie das Modul im Shopadmin unter "Erweiterungen -> Module".

## Verwendung

Dieses Paket enthält keine Exporte oder Aktionen. In [Erweiterungspakete](#erweiterungspakete) finden Sie installierbare Beispieleinträge.

Melden Sie sich im Adminbereich Ihres Shops an und navigieren Sie zu "D³ Module -> Data Wizard". Wechseln Sie je nach Wunsch zu "Exporte" oder "Aktionen". Wählen Sie den gewünschten Eintrag und starten Sie den Export bzw. die Aktion.

## Erweiterbarkeit

Das Modul stellt das technische Grundgerüst der Exporte dar und erhebt keinen Anspruch auf Vollständigkeit. Um den Umfang an die individuellen Anforderungen anzupassen, sind folgende Erweiterungen vorbereitet:

- Exporte oder Tasks hinzufügen
- Verwendung bestehender und neuer Gruppen
- Exportformate ergänzen

Unabhängig dessen stehen alle Erweiterungsmöglichkeiten zur Verfügung, die der OXID Shop für Module bereitstellt.

### Erweiterungspakete

- `d3/datawizardtasks` - liefert Beispielexporte und deren Implementierungsreferenz
- `d3/datawizardcli` - ermöglicht die Ausführung der Exporte oder Tasks über die Eingabeaufforderung (z.B. als Cronjobs)
- `d3/datawizardlink` - stellt URLs zur Verfügung, um Exporte von Drittsystemen generieren zu lassen

## Changelog

Siehe [CHANGELOG](CHANGELOG.md) für weitere Informationen.

## Beitragen

Wenn Sie eine Verbesserungsvorschlag haben, legen Sie einen Fork des Respoitories an und erstellen Sie einen Pull Request. Alternativ können Sie einfach ein Issue erstellen. Fügen Sie das Projekt zu Ihren Favoriten hinzu. Vielen Dank.

- Erstellen Sie einen Fork des Projekts
- Erstellen Sie einen Feature Branch (git checkout -b feature/AmazingFeature)
- Fügen Sie Ihre Änderungen hinzu (git commit -m 'Add some AmazingFeature')
- Übertragen Sie den Branch (git push origin feature/AmazingFeature)
- Öffnen Sie einen Pull Request

## Lizenz
(Stand: 06.05.2021)

Vertrieben unter der GPLv3 Lizenz.

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
