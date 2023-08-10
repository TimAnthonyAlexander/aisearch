<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

while (true) {
    print "Search: ";
    $term = trim(fgets(STDIN));

    if ($term === 'q') {
        break;
    }
}
