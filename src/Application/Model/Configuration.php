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

namespace D3\DataWizard\Application\Model;

use D3\DataWizard\Application\Model\Exports\InactiveCategories;
use D3\DataWizard\Application\Model\Exports\KeyFigures;
use OxidEsales\Eshop\Core\Registry;

class Configuration
{
    const GROUP_SHOP     = 'D3_DATAWIZARD_GROUP_SHOP';
    const GROUP_CATEGORY = 'D3_DATAWIZARD_GROUP_CATEGORIES';
    const GROUP_ARTICLES = 'D3_DATAWIZARD_GROUP_ARTICLES';
    const GROUP_USERS    = 'D3_DATAWIZARD_GROUP_USERS';
    const GROUP_ORDERS   = 'D3_DATAWIZARD_GROUP_ORDERS';
    const GROUP_REMARKS  = 'D3_DATAWIZARD_GROUP_REMARKS';

    protected $actions = [];
    protected $exports = [];

    public function __construct()
    {
        $this->configure();
    }

    public function configure()
    {
        if (false === Registry::getConfig()->getConfigParam('d3datawizard_hideexamples', false)) {
            $this->registerExport(self::GROUP_CATEGORY, oxNew(InactiveCategories::class));
            $this->registerExport(self::GROUP_SHOP, oxNew(KeyFigures::class));
        }
    }

    /**
     * @param            $group
     * @param ActionBase $action
     */
    public function registerAction($group, ActionBase $action)
    {
        $this->actions[$group][md5(serialize($action))] = $action;
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
    public function getGroupedActions(): array
    {
        return $this->actions;
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

    public function getActionsByGroup($group)
    {
        return $this->actions[$group];
    }

    public function getExportsByGroup($group)
    {
        return $this->exports[$group];
    }

    /**
     * @return array
     */
    public function getAllActions() : array
    {
        $all = [];

        foreach ($this->getGroups() as $group) {
            $all = array_merge($all, $this->getActionsByGroup($group));
        }

        return $all;
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
     * @return ActionBase
     */
    public function getActionById($id) : ActionBase
    {
        return $this->getAllExports()[$id];
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
