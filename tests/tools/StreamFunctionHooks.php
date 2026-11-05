<?php

declare(strict_types=1);

namespace D3\DataWizard\Application\Model;

// Loaded only by isolated tests; production calls resolve to PHP's native functions.
function header(string $value): void
{
    $GLOBALS['dataWizardTestHeaders'][] = $value;
}

function clearstatcache(bool $clearRealpathCache = false, string $filename = ''): void
{
    $GLOBALS['dataWizardTestStatCacheClears'][] = [$clearRealpathCache, $filename];
    \clearstatcache($clearRealpathCache, $filename);
}
