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
use D3\DataWizard\Application\Model\Exports\activeArticlesInactiveCategory;
use D3\DataWizard\Application\Model\Exports\articlesWithoutManufacturers;
use D3\DataWizard\Application\Model\Exports\emptyCategories;
use D3\DataWizard\Application\Model\Exports\gappedArticleImages;
use D3\DataWizard\Application\Model\Exports\inactiveCategories;
use D3\DataWizard\Application\Model\Exports\inactiveParentCategory;
use D3\DataWizard\Application\Model\Exports\noArticleTextSet;
use D3\DataWizard\Application\Model\Exports\unreleasedRatings;
use D3\DataWizard\Application\Model\Exports\wrongArticlePrice;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;

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

    public function doExport()
    {
        $id = Registry::getRequest()->getRequestEscapedParameter('exportid');
        $this->configuration->getExportById($id)->run();

        $oEx = oxNew(
            StandardException::class,
            Registry::getLang()->translateString('D3_DATAWIZARD_ERR_NOEXPORTCONTENT')
        );
        Registry::get(UtilsView::class)->addErrorToDisplay($oEx);
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