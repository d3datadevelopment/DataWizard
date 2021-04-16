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

namespace D3\DataWizard\Application\Model;

use D3\DataWizard\Application\Model\Exports\activeArticlesInactiveCategory;
use D3\DataWizard\Application\Model\Exports\articlesWithoutManufacturers;
use D3\DataWizard\Application\Model\Exports\emptyCategories;
use D3\DataWizard\Application\Model\Exports\gappedArticleImages;
use D3\DataWizard\Application\Model\Exports\inactiveCategories;
use D3\DataWizard\Application\Model\Exports\inactiveParentCategory;
use D3\DataWizard\Application\Model\Exports\noArticleTextSet;
use D3\DataWizard\Application\Model\Exports\unreleasedRatings;
use D3\DataWizard\Application\Model\Exports\wrongArticlePrice;

class Configuration
{
    const GROUP_CATEGORY = 'D3_DATAWIZARD_GROUP_CATEGORIES';
    const GROUP_ARTICLES = 'D3_DATAWIZARD_GROUP_ARTICLES';
    const GROUP_REMARKS  = 'D3_DATAWIZARD_GROUP_REMARKS';

    protected $exports = [];

    public function __construct()
    {
        $this->configure();
    }

    public function configure()
    {

    }

    public function registerExport($group, ExportBase $export)
    {
        $this->exports[$group][md5(serialize($export))] = $export;
    }

    public function getGroupedExports()
    {
        return $this->exports;
    }

    public function getGroups()
    {
        return array_keys($this->exports);
    }

    public function getExportsByGroup($group)
    {
        return $this->exports[$group];
    }

    /**
     * @return array
     */
    public function getAllExports() : array
    {
        $all = [];

        foreach ($this->getGroups() as $group) {
            $all = array_merge($all, $this->getExportsByGroup($group));
        }

        return $all;
    }

    /**
     * @param $id
     *
     * @return ExportBase
     */
    public function getExportById($id) : ExportBase
    {
        return $this->getAllExports()[$id];
    }
}