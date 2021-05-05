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

namespace D3\DataWizard\Application\Controller\Admin;

use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\ModCfg\Application\Model\d3database;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class d3ActionWizard extends AdminDetailsController
{
    protected $_sThisTemplate = 'd3ActionWizard.tpl';

    /** @var Configuration */
    protected $configuration;

    public function __construct()
    {
        parent::__construct();

        $this->configuration = oxNew(Configuration::class);
    }

    public function getGroups()
    {
        return $this->configuration->getGroups();
    }

    public function getGroupActions($group)
    {
        return $this->configuration->getActionsByGroup($group);
    }

    /**
     * @throws DatabaseConnectionException
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     */
    public function doAction()
    {
        try {
            $id = Registry::getRequest()->getRequestEscapedParameter('actionid');
            $action = $this->configuration->getActionById($id);

            [ $queryString, $parameters ] = $action->getQuery();

            if (Registry::getConfig()->getConfigParam('d3datawizard_debug')) {
                throw oxNew(
                    DebugException::class,
                    d3database::getInstance()->getPreparedStatementQuery($queryString, $parameters)
                );
            }

            $action->run();
        } catch (DataWizardException|DBALException|DatabaseErrorException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e);
        }
    }

    public function getUserMessages()
    {
        return null;
    }

    public function getHelpURL()
    {
        return null;
    }
}