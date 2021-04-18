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

namespace D3\DataWizard\Application\Controller\Admin;

use D3\DataWizard\Application\Model\Configuration;
use Doctrine\DBAL\DBALException;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
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
     * @throws CannotInsertRecord
     * @throws DBALException
     * @throws Exception
     */
    public function doExport()
    {
        try {
            $id = Registry::getRequest()->getRequestEscapedParameter('exportid');
            $export = $this->configuration->getExportById($id);

            if (Registry::getConfig()->getConfigParam('d3datawizard_debug')) {
                throw oxNew(
                    StandardException::class,
                    $export->getQuery()
                );
            }

            $export->run();
        } catch (StandardException $e) {
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