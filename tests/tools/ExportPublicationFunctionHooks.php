<?php

declare(strict_types=1);

namespace D3\DataWizard\Application\Model;

// Loaded only in isolated tests; normal exports use PHP's native functions.
function fwrite($stream, string $data): int|false
{
    if (($GLOBALS['dataWizardPublicationFailure'] ?? '') === 'write') {
        return false;
    }
    if (($GLOBALS['dataWizardPublicationFailure'] ?? '') === 'partial') {
        return \fwrite($stream, substr($data, 0, 2));
    }

    return \fwrite($stream, $data);
}

function link(string $target, string $link): bool
{
    $failure = $GLOBALS['dataWizardPublicationFailure'] ?? '';
    if ($failure === 'link') {
        return false;
    }
    if ($failure === 'collision') {
        $GLOBALS['dataWizardPublicationCandidates'][] = $link;
        \file_put_contents($link, 'concurrent');
        return false;
    }

    return \link($target, $link);
}
