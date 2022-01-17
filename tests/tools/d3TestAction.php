<?php

namespace D3\DataWizard\tests\tools;

use D3\DataWizard\Application\Model\ActionBase;

class d3TestAction extends ActionBase
{
    public function getTitle(): string
    {
        return 'TestTitle';
    }

    public function getQuery(): array
    {
        return ["UPDATE 1"];
    }
}
