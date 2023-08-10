<?php

declare(strict_types=1);

use TimAlexander\Aisearch\SystemConfig\SystemConfig;

require_once __DIR__ . '/vendor/autoload.php';

$systemConfig = new SystemConfig();

while (true) {
    print "Search: ";
    $term = trim(fgets(STDIN));

    if ($term === 'q') {
        break;
    }
}
