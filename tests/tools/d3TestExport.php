<?php

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