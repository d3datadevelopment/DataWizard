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

declare(strict_types=1);

namespace D3\DataWizard\Application\Model\Exceptions;

use D3\DataWizard\Application\Model\QueryBase;
use Exception;
use FormManager\Inputs\Input;
use OxidEsales\Eshop\Core\Exception\StandardException;

class InputUnvalidException extends DataWizardException
{
    /** @var QueryBase */
    public $task;

    public function __construct( QueryBase $task, Input $inputElement, $iCode = 0, Exception $previous = null )
    {
        $messages = [];
        foreach ($inputElement->getError()->getIterator() as $item) {
            $messages[] = $inputElement->label->innerHTML.' -> '.$item->getMessage();
        }

        $sMessage = implode(
            ' - ',
            [
                $task->getTitle(),
                implode(', ', $messages)
            ]
        );
        parent::__construct( $sMessage, $iCode, $previous );

        $this->task = $task;
    }
}