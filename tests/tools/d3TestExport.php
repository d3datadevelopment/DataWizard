<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

namespace D3\DataWizard\tests\tools;

use D3\DataWizard\Application\Model\ExportBase;

class d3TestExport extends ExportBase
{
    public function getTitle(): string
    {
        return 'TestTitle';
    }

    public function getQuery(): array
    {
        return ["SELECT 1"];
    }
}
