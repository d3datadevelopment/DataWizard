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

class Configuration implements ConfigurationInterface
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

    public function __construct(iterable $actions = [], iterable $exports = [])
    {
        foreach ($actions as $action) {
            if (!$action instanceof ActionBase) {
                throw new \InvalidArgumentException('Action must extend ActionBase');
            }
            $this->addTask($this->actions, $action);
        }
        foreach ($exports as $export) {
            if (!$export instanceof ExportBase) {
                throw new \InvalidArgumentException('Export must extend ExportBase');
            }
            $this->addTask($this->exports, $export);
        }
    }

    private function addTask(array &$groups, TaskRegistrationInterface $task): void
    {
        $id = $task->getTaskId();
        $group = $task->getGroup();
        // IDs are used in HTML attributes and JavaScript, and groups in CSS selectors.
        if (!preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/D', $id) ||
            !preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/D', $group)) {
            throw new \InvalidArgumentException('Invalid task ID or group: ' . $id . ', ' . $group);
        }
        foreach ($groups as $tasks) {
            if (isset($tasks[$id])) {
                throw new \InvalidArgumentException('Duplicate task ID: ' . $id);
            }
        }
        $groups[$group][$id] = $task;
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

        if (!in_array($id, array_keys($allActions)) || !$allActions[ $id ]) {
            throw oxNew(DataWizardException::class, 'no action with id '.$id, []);
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

        if (!in_array($id, array_keys($allExports)) || !$allExports[ $id ]) {
            throw oxNew(DataWizardException::class, 'no export with id '.$id, []);
        }

        return $allExports[$id];
    }
}
