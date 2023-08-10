<?php

declare(strict_types=1);

use TimAlexander\Aisearch\Search\Search;
use TimAlexander\Aisearch\SystemConfig\SystemConfig;

require_once __DIR__ . '/vendor/autoload.php';

$systemConfig = new SystemConfig();

while (!$systemConfig->isset('os')) {
    print "What operating system are you using (linux, mac, windows)? ";
    $os = trim(fgets(STDIN));

    if ($os === 'q') {
        exit;
    }

    if (!in_array($os, ['linux', 'mac', 'windows'])) {
        continue;
    }

    $systemConfig->set('os', $os);
}

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

    $quit = false;

    while (!$quit) {
        foreach ($results as $index => $result) {
            printf("[%d] %s\n", $index, $result);
        }

        print "Which result do you want to open (q to quit): ";
        $selection = trim(fgets(STDIN));

        if ($selection === 'q') {
            $quit = true;
            continue;
        }

        if (!isset($results[$selection])) {
            print "Invalid selection.\n";
            continue;
        }

        $result = $results[$selection];

        print "Opening $result...\n";
        shell_exec("open \"$result\"");
    }
}
