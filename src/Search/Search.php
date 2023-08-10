<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\Search;

use TimAlexander\Aisearch\SystemConfig\SystemConfig;

class Search
{
    private array $results = [];

    public function __construct(
        private SystemConfig $systemConfig,
        public readonly string $query,
    ) {
    }

    public function executeSearch(): void
    {
        $this->results = [];
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
