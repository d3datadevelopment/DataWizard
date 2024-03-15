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

use D3\DataWizard\Application\Model\Exceptions\DataWizardException;

class Configuration
{
    public const GROUP_SHOP     = 'D3_DATAWIZARD_GROUP_SHOP';
    public const GROUP_CATEGORY = 'D3_DATAWIZARD_GROUP_CATEGORIES';
    public const GROUP_ARTICLES = 'D3_DATAWIZARD_GROUP_ARTICLES';
    public const GROUP_USERS    = 'D3_DATAWIZARD_GROUP_USERS';
    public const GROUP_ORDERS   = 'D3_DATAWIZARD_GROUP_ORDERS';
    public const GROUP_REMARKS  = 'D3_DATAWIZARD_GROUP_REMARKS';
    public const GROUP_CMS      = 'D3_DATAWIZARD_GROUP_CMS';

    protected array $actions = [];
    protected array $exports = [];

    public function __construct()
    {
        $this->configure();
    }

    public function configure()
    {
        // extend to add exports and actions via 'registerAction()' or 'registerExport()' method
    }

    /**
     * @param            $group
     * @param ActionBase $action
     */
    public function registerAction($group, ActionBase $action): void
    {
        $this->actions[$group][md5(get_class($action))] = $action;
    }

    /**
     * @param            $group
     * @param ExportBase $export
     */
    public function registerExport($group, ExportBase $export): void
    {
        $this->exports[$group][md5(get_class($export))] = $export;
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
    public function getActionGroups(): array
    {
        return array_keys($this->actions);
    }

    /**
     * @return array
     */
    public function getExportGroups(): array
    {
        return array_keys($this->exports);
    }

    /**
     * @param $group
     *
     * @return array
     */
    public function getActionsByGroup($group): array
    {
        return $this->actions[$group];
    }

    /**
     * @param $group
     *
     * @return array
     */
    public function getExportsByGroup($group): array
    {
        return $this->exports[$group];
    }

    /**
     * @return array
     */
    public function getAllActions(): array
    {
        $all = [];

        foreach ($this->getActionGroups() as $group) {
            $all = array_merge($all, $this->getActionsByGroup($group));
        }

        return $all;
    }

    /**
     * @return array
     */
    public function getAllExports(): array
    {
        $all = [];

        foreach ($this->getExportGroups() as $group) {
            $all = array_merge($all, $this->getExportsByGroup($group));
        }

        return $all;
    }

    /**
     * @param $id
     *
     * @return ActionBase
     */
    public function getActionById($id): ActionBase
    {
        $allActions = $this->getAllActions();

        if ( ! $allActions[ $id ] ) {
            throw oxNew(DataWizardException::class, 'no action with id '.$id);
        }

        return $allActions[$id];
    }

    /**
     * @param $id
     *
     * @return ExportBase
     */
    public function getExportById($id): ExportBase
    {
        $allExports = $this->getAllExports();

        if ( ! $allExports[ $id ] ) {
            throw oxNew(DataWizardException::class, 'no export with id '.$id);
        }

        return $allExports[$id];
    }
}
