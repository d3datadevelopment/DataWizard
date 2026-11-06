# Eigene Actions und Exports

Actions und Exports werden als Services im eigenen Modul registriert. Eine Action
erweitert `ActionBase`, ein Export `ExportBase`. Beide implementieren darüber
`TaskRegistrationInterface` und geben eine stabile ID und eine Kategorie an:

```php
public function getTaskId(): string
{
    return 'acme_order_report';
}

public function getGroup(): string
{
    return \D3\DataWizard\Application\Model\Configuration::GROUP_ORDERS;
}
```

`getGroup()` liefert eine Übersetzungs-ID. Die Konstanten in `Configuration`
enthalten die vorhandenen Kategorien; eigene Übersetzungs-IDs sind möglich, wenn
das Modul die Übersetzungen bereitstellt. Jeder Task hat genau eine Kategorie.
ID und Kategorie müssen mit einem ASCII-Buchstaben beginnen und dürfen anschließend
ASCII-Buchstaben, Ziffern, Unterstriche und Bindestriche enthalten. Die Task-ID
muss innerhalb ihres Typs eindeutig sein; eine Action und ein Export dürfen die
gleiche ID verwenden. Ungültige oder doppelte IDs führen beim Aufbau des Katalogs
zu einer `InvalidArgumentException`.

Die `services.yaml` des Moduls registriert den Task mit dem passenden Tag:

```yaml
services:
  Acme\Example\OrderReport:
    factory: 'oxNew'
    shared: false
    arguments: ['Acme\Example\OrderReport']
    tags: ['d3.datawizard.export']
```

Für Actions lautet der Tag `d3.datawizard.action`. Die Referenzimplementierungen
stehen in `d3/datawizardtasks`. Die Reihenfolge ergibt sich aus der Reihenfolge der
getaggten Services; Symfony-Tag-Prioritäten können diese beeinflussen. Die Tasks
werden beim Erstellen des Katalogs instanziiert. Da sie Formular- und Requestzustand
enthalten können, sollten sie nicht als geteilte Services registriert werden.

Nach Änderungen an Modulaktivierung oder Service-Konfiguration muss der
OXID-Container neu aufgebaut werden. Die bisherige Überladung von
`Configuration::configure()` und die daraus abgeleiteten Klassen-Hashes als
Task-IDs entfallen.
