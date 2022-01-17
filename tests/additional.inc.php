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

// Include datawizard test config

namespace D3\DataWizard\tests;

use D3\ModCfg\Tests\additional_abstract;
use Exception;
use OxidEsales\Eshop\Core\Exception\StandardException;

include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'd3datawizard_config.php');

class additional extends additional_abstract
{
    /**
     * additional constructor.
     * @throws StandardException
     */
    public function __construct()
    {
        if (D3DATAWIZARD_REQUIRE_MODCFG) {
            $this->reactivateModCfg();
        }
    }
}

try {
    d3GetModCfgDIC()->get(additional::class);
} catch (Exception $e) {
}
