# Eigene Export-Renderer

Ein Modul kann dem Datawizard über einen Symfony-Service und den Tag
`d3.datawizard.renderer` ein Exportformat hinzufügen. Der Service muss
`D3\DataWizard\Application\Model\ExportRenderer\RendererInterface` implementieren.

## Renderer implementieren

Das Beispiel gibt die Daten als JSON Lines aus, eine JSON-Zeile pro Datensatz:

```php
<?php

declare(strict_types=1);

namespace Acme\Example;

use D3\DataWizard\Application\Model\ExportRenderer\RendererInterface;

final class JsonLinesRenderer implements RendererInterface
{
    public function getFormat(): string
    {
        return 'ACME_JSONL';
    }

    public function getTitleTranslationId(): string
    {
        return 'ACME_EXPORT_FORMAT_JSONL';
    }

    public function getFileExtension(): string
    {
        return 'jsonl';
    }

    public function getContent(iterable $rows, iterable $fieldNames): string
    {
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = json_encode($row, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }
        return implode("\n", $lines) . ($lines ? "\n" : '');
    }
}
```

`$rows` enthält die Ergebniszeilen, `$fieldNames` die Spaltennamen. Beide Argumente
sind iterierbar und müssen keine Arrays sein. Der Renderer liefert den gesamten
Dateiinhalt als String; `getFileExtension()` liefert die Endung ohne Punkt.
Die Übersetzungs-ID muss das eigene Modul für die unterstützten Sprachen bereitstellen.

## Service registrieren

In der `services.yaml` des eigenen OXID-Moduls:

```yaml
services:
  Acme\Example\JsonLinesRenderer:
    tags: ['d3.datawizard.renderer']
```

Benötigt der Renderer Abhängigkeiten, werden diese wie bei anderen Services in der
Definition angegeben. Das Modul muss aktiviert und der Container nach Änderungen an
der Service-Konfiguration neu aufgebaut sein.

## Formatkennung und Verhalten

- `getFormat()` liefert eine nicht leere, modulweit eindeutige Kennung. Der Vergleich
  erfolgt ohne Berücksichtigung der Groß- und Kleinschreibung. Leere oder doppelte
  Kennungen lösen beim Erstellen des Katalogs eine `InvalidArgumentException` aus.
- `QUERY` ist für die Abfragevorschau reserviert und darf nicht als Rendererkennung
  verwendet werden. Die eingebauten Formate sind `CSV`, `CSV_SPREADSHEET`, `Pretty`
  und `JSON`.
- Die Kennung erscheint in der Formatauswahl und wird für den Lookup verwendet;
  die sichtbare Bezeichnung stammt aus `getTitleTranslationId()`.
- Eigene Renderer werden derzeit über `getContent()` ausgeführt. Auch eine
  Implementierung von `StreamingRendererInterface` aktiviert für eigene Renderer
  noch kein Streaming: Der Exportpfad beschränkt dieses aktuell auf die eingebauten
  Renderer-Klassen. Große Exporte können daher viel Speicher benötigen.

Die Registry ist über `RendererBridgeInterface` erreichbar. Ein unbekanntes Format
führt bei `getRenderer()` zu einer `NoSuitableRendererException`.