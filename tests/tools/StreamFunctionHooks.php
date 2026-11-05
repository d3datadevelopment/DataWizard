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
