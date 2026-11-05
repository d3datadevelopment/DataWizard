<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 */

declare(strict_types=1);

namespace D3\DataWizard\Application\Model;

class ActionResult
{
    public function __construct(private readonly int $affectedRows)
    {
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }
}
