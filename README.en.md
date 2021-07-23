> [deutsche Version](README.md)

# D³ Data Wizard for OXID eShop

The module `DataWizard` offers a framework for the simple integration of exports via the admin area of the OXID shop.

The exports are defined via database queries or ready-made data lists. Various export formats are available. The generation is possible at any time and always recurring (within the system limits). These are offered as downloads in the browser.

All exports are grouped together for better clarity.

Sample exports are included in the scope of delivery. These are intended to serve as an implementation reference for individual exports. The display of the sample exports can be deactivated in the basic module settings.

## Installation

In the console in the shop root (above source and vendor), execute the following command:

```bash
php composer require d3/datawizard
``` 

Activate the module in the admin area of the shop in "Extensions -> Modules".

## Extensibility

The module represents the technical basic framework of the exports and does not claim to be complete. In order to adapt the scope to individual requirements, the following extensions are prepared:

- Add exports
- Use existing and new groups
- Add export formats

Independently of this, all extension options are available that the OXID Shop provides for modules.

### Add exports

#### Define export

Each export is defined in a separate class. This export class must extend the class `D3\DataWizard\Application\Model\ExportBase`. All necessary functions are predefined in it. The following methods are available:

##### mandatory method calls:
- getTitle() - defines the title in the admin area and the base of the later export file name.
- getQuery() - contains the query as a prepared statement that defines the data to be exported

##### optional method calls:
- getDescription() - contains a short additional description of the export, this will be shown in the admin area
- getButtonText() - defines the text of the submit button in the admin area
- getExportFilenameBase() - defines the base of the later export filename
- executeQuery() - returns the compiled export data
- further methods, the adaptation of which, however, can lead to changed module behaviour and should therefore only be changed with caution

#### Register exports

In order to be able to use the created export in the module, it must be registered. For this purpose there is the class `D3\DataWizard\Application\Model\Configuration`. This is to be overloaded with the possibilities of the OXID shop and the method `configure()` contained therein is to be supplemented. The following call is available for registering an export:

```
$this->registerExport(self::GROUP_CATEGORY, oxNew(myCustomExport::class));
```

The first parameter contains the language identifier for the export group. Typical identifiers are already prepared in constants of the configuration class. The instance of the export class is expected as the 2nd parameter.

## Changelog

See [CHANGELOG](CHANGELOG.md) for further informations.

## Licence of this software (d3/datawizard)
(status: 2021-05-06)

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

This software is distributed under the GNU GENERAL PUBLIC LICENSE version 3.
```

For full copyright and licensing information, please see the [LICENSE](LICENSE.md) file distributed with this source code.

## Further licences and terms of use

### background gradients on src/Application/views/admin/tpl/ templates
(https://www.gradientmagic.com/licensing - status: 07.05.2021)

```
Image courtesy of gradientmagic.com

Free Gradients

Gradients available on the site are free to use on personal and commercial projects, with attribution.
```

-------------------------------------------------------------------------------

The following software packages are not part of this module. However, they are required for use. The linked packages are under the following licences:

### league/csv [MIT]
(https://github.com/thephpleague/csv - status: 2021-05-06)

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
(https://github.com/viossat/arraytotexttable - status: 2021-05-06)

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
