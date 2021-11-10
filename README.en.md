[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# D³ Data Wizard for OXID eShop

The module `DataWizard` offers a framework for the simple integration of exports via the admin area of the OXID shop.

The exports are defined via database queries or ready-made data lists. Various export formats are available. The generation is possible at any time and always recurring (within the system limits). These are offered as downloads in the browser.

All exports and tasks are grouped together for better clarity.

Sample exports are included in the package d3/datawizardtasks. These are intended to serve as an implementation reference for individual exports.

![administration area](assets/administration_exports.jpg "administration area")

## Installation

In the console in the shop root (above source and vendor), execute the following command:

```bash
php composer require d3/datawizard
``` 

Activate the module in the admin area of the shop in "Extensions -> Modules".

## Extensibility

The module represents the technical basic framework of the exports and does not claim to be complete. In order to adapt the scope to individual requirements, the following extensions are prepared:

- Add exports or tasks
- Use existing and new groups
- Add export formats

Independently of this, all extension options are available that the OXID Shop provides for modules.

## Extension packages

- `d3/datawizardtasks` - provides sample exports and their implementation reference
- `d3/datawizardcli` - provides the execution of exports or tasks via the command prompt (e.g. as cronjobs)
- `d3/datawizardlink` - provides URLs to generate exports from third party systems

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
