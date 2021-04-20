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

use Exception;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class DebugException extends StandardException implements DataWizardException
{
    public function __construct($sMessage = "not set", $iCode = 0, Exception $previous = null )
    {
        $sMessage = sprintf(
            Registry::getLang()->translateString('D3_DATAWIZARD_DEBUG'),
            $sMessage
        );

        parent::__construct($sMessage, $iCode, $previous );
    }
}