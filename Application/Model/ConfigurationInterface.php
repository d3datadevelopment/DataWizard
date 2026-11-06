<?php

declare(strict_types=1);

namespace D3\DataWizard\Application\Model;

interface ConfigurationInterface
{
    public function getGroupedActions(): array;
    public function getGroupedExports(): array;
    public function getActionGroups(): array;
    public function getExportGroups(): array;
    public function getActionsByGroup($group): array;
    public function getExportsByGroup($group): array;
    public function getAllActions(): array;
    public function getAllExports(): array;
    public function getActionById($id): ActionBase;
    public function getExportById($id): ExportBase;
}
