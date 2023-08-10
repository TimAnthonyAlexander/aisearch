<?php

declare(strict_types=1);

use TimAlexander\Aisearch\Search\Search;
use TimAlexander\Aisearch\SystemConfig\SystemConfig;

require_once __DIR__ . '/vendor/autoload.php';

$systemConfig = new SystemConfig();

while (true) {
    print "Search: ";
    $term = trim(fgets(STDIN));

    if ($term === 'q') {
        break;
    }

    $search = new Search($systemConfig, $term);

    $search->executeSearch();

    $results = $search->getResults();

    if (count($results) === 0) {
        print "No results found.\n";
        continue;
    }

    print json_encode($results, JSON_PRETTY_PRINT);
}
