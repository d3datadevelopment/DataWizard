[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# D³ Data Wizard for OXID eShop

The module `DataWizard` offers a framework for the simple integration of exports via the admin area of the OXID shop.

The exports are defined via database queries or ready-made data lists. Various export formats are available. The generation is possible at any time and always recurring (within the system limits). These are offered as downloads in the browser.

All exports and tasks are grouped together for better clarity.

Sample exports are included in the package `d3/datawizardtasks`. These are intended to serve as an implementation reference for individual exports.

![administration area](assets/administration_exports.jpg "administration area")

## Table of content

- [Installation](#installation)
- [Usage](#usage)
- [Extensibility](#extensibility)
    - [Extension packages](#extension-packages)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [License](#license)
- [Further licences and terms of use](#further-licences-and-terms-of-use)

## Installation

This package requires an Composer installed OXID eShop as defined in [composer.json](composer.json).

Open a command line interface and navigate to the shop root directory (parent of source and vendor). Execute the following command. Adapt the paths to your environment.

```bash
php composer require d3/datawizard:^3.0
``` 

Activate the module in the admin area of the shop in "Extensions -> Modules".

## Usage

These package doesn't contain any export or action items. See [Extension packages](#extension_packages) for installable example items.

Log in to the admin area of your shop and navigate to "D³ Modules -> Data Wizard". Go to "Exports" or "Actions" (as desired). Choose your desired item and export or execute it.

### CSV and spreadsheets

The primary export button in the admin area uses `CSV (for spreadsheets)`; admin requests without a format use it as well. The format menu still offers `CSV` (raw data), which leaves field names and values unchanged for machine processing. Raw CSV does not protect spreadsheet users from formulas. Programmatic calls continue to default to `CSV`.

The technical name of the spreadsheet format is `CSV_SPREADSHEET`. It prefixes suspicious field names and values with an apostrophe, including formula characters following leading whitespace or control characters. This changes the exported data: for example, the text `-123` becomes `'-123`. The CSV writer still handles delimiters and quotation marks.

Behavior depends on the spreadsheet application and its import and save settings. In particular, saving or exporting again can change the protective prefix; the format cannot guarantee safety in every application or subsequent processing step. Use the raw format when unchanged data is required.

### Large exports

The built-in `CSV`, `CSV_SPREADSHEET` and `JSON` formats process result rows incrementally for normal file and browser exports, using a temporary file. The temporary directory must have sufficient disk space. Depending on the database driver and its configuration, the driver may still buffer query results, so memory usage is not necessarily constant. `Pretty`, direct calls to `getContent()`, and customized export or renderer classes continue to use the existing in-memory path.

For exports to a target directory, the completed file is published atomically using a hard link without overwriting existing files. The target directory must support hard links and have sufficient space for the temporary file. An abrupt process termination may leave `.d3export_*.tmp` files behind; temporary files that are no longer in use can be removed.

## Extensibility

After a standard action completes successfully, `getActionResult()` returns an `ActionResult` whose `getAffectedRows()` method reports the affected row count. Before execution and after an error the result is `null`; errors continue to be reported as exceptions. The signatures of `run()` and `executeAction()` are unchanged. Custom actions overriding these methods can set the protected `$actionResult` property themselves.

The module represents the technical basic framework of the exports and does not claim to be complete. In order to adapt the scope to individual requirements, the following extensions are prepared:

- Add exports or tasks
- Use existing and new groups
- Add export formats

Independently of this, all extension options are available that the OXID Shop provides for modules.

### Extension packages

- `d3/datawizardtasks` - provides sample exports and their implementation reference
- `d3/datawizardcli` - provides the execution of exports or tasks via the command prompt (e.g. as cronjobs)
- `d3/datawizardlink` - provides URLs to generate exports from third party systems

## Changelog

See [CHANGELOG](CHANGELOG.md) for further informations.

## Contributing

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue. Don't forget to give the project a star! Thanks again!

- Fork the Project
- Create your Feature Branch (git checkout -b feature/AmazingFeature)
- Commit your Changes (git commit -m 'Add some AmazingFeature')
- Push to the Branch (git push origin feature/AmazingFeature)
- Open a Pull Request

## License
(status: 2021-05-06)

Distributed under the GPLv3 license.

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
