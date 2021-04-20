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

declare(strict_types=1);

namespace D3\DataWizard\Application\Model\Exceptions;

use Exception;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class NoSuitableRendererException extends StandardException implements DataWizardException
{
    public function __construct($sMessage = "not set", $iCode = 0, Exception $previous = null )
    {
        $sMessage = sprintf(
            Registry::getLang()->translateString('D3_DATAWIZARD_ERR_NOSUITABLERENDERER'),
            $sMessage
        );

        parent::__construct($sMessage, $iCode, $previous );
    }
}