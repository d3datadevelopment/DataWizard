<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace D3\DataWizard\Application\Model\Exceptions;

use D3\DataWizard\Application\Model\QueryBase;
use Exception;
use OxidEsales\Eshop\Core\Exception\StandardException;

class TaskException extends StandardException implements DataWizardException
{
    /** @var QueryBase */
    public $task;

    public function __construct( QueryBase $task, $sMessage = "not set", $iCode = 0, Exception $previous = null )
    {
        $sMessage = implode(
            ' - ',
            [
                $task->getTitle(),
                $sMessage
            ]
        );
        parent::__construct( $sMessage, $iCode, $previous );

        $this->task = $task;
    }
}