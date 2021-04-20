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

namespace D3\DataWizard\Application\Model;

class Configuration
{
    const GROUP_CATEGORY = 'D3_DATAWIZARD_GROUP_CATEGORIES';
    const GROUP_ARTICLES = 'D3_DATAWIZARD_GROUP_ARTICLES';
    const GROUP_USERS    = 'D3_DATAWIZARD_GROUP_USERS';
    const GROUP_ORDERS   = 'D3_DATAWIZARD_GROUP_ORDERS';
    const GROUP_REMARKS  = 'D3_DATAWIZARD_GROUP_REMARKS';

    protected $exports = [];

    public function __construct()
    {
        $this->configure();
    }

    public function configure()
    {

    }

    /**
     * @param            $group
     * @param ExportBase $export
     */
    public function registerExport($group, ExportBase $export)
    {
        $this->exports[$group][md5(serialize($export))] = $export;
    }

    /**
     * @return array
     */
    public function getGroupedExports(): array
    {
        return $this->exports;
    }

    /**
     * @return array
     */
    public function getGroups(): array
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