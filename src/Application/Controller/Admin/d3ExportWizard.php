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

namespace D3\DataWizard\Application\Controller\Admin;

use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class d3ExportWizard extends AdminDetailsController
{
    protected $_sThisTemplate = 'd3ExportWizard.tpl';

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

    public function getGroupExports($group)
    {
        return $this->configuration->getExportsByGroup($group);
    }

    /**
     * @throws DatabaseConnectionException
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     */
    public function doExport()
    {
        try {
            $id = Registry::getRequest()->getRequestEscapedParameter('exportid');
            $export = $this->configuration->getExportById($id);

            if (Registry::getConfig()->getConfigParam('d3datawizard_debug')) {
                throw oxNew(
                    DebugException::class,
                    $export->getQuery()
                );
            }

            $export->run(Registry::getRequest()->getRequestEscapedParameter('exportformat'));
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