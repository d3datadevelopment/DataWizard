<?php

declare(strict_types=1);

namespace D3\DataWizard\Application\Model;

interface TaskRegistrationInterface
{
    public function getTaskId(): string;

    /** @return non-empty-string Translation ID of the task group. */
    public function getGroup(): string;
}