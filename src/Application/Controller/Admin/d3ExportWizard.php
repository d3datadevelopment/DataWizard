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

    public function doExport()
    {
        $id = Registry::getRequest()->getRequestEscapedParameter('exportid');
        $this->configuration->getExportById($id)->run();
    }

    public function getUserMessages()
    {
        return null;
    }

    public function getHelpURL()
    {
        return null;
    }

    public function exportEmptyCategories()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\emptyCategories $export */
        $export = oxNew(emptyCategories::class);
        $export->run();
    }

    public function exportInactiveCategories()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\inactiveCategories $export */
        $export = oxNew(inactiveCategories::class);
        $export->run();
    }

    public function exportGappedArticleImages()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\gappedArticleImages $export */
        $export = oxNew(gappedArticleImages::class);
        $export->run();
    }

    public function exportNoArticleTextsSet()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\noArticleTextSet $export */
        $export = oxNew(noArticleTextSet::class);
        $export->run();
    }

    public function exportWrongArticlePrice()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\wrongArticlePrice $export */
        $export = oxNew(wrongArticlePrice::class);
        $export->run();
    }

    public function exportArticlesWithoutManufacturers()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\articlesWithoutManufacturers $export */
        $export = oxNew(articlesWithoutManufacturers::class);
        $export->run();
    }

    public function exportUnreleasedRatings()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\unreleasedRatings $export */
        $export = oxNew(unreleasedRatings::class);
        $export->run();
    }

    public function exportInactiveParentCategory()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\inactiveParentCategory $export */
        $export = oxNew(inactiveParentCategory::class);
        $export->run();
    }

    public function exportActiveArticlesInactiveCategory()
    {
        /** @var \D3\DataWizard\Application\Model\Exports\activeArticlesInactiveCategory $export */
        $export = oxNew(activeArticlesInactiveCategory::class);
        $export->run();
    }
}