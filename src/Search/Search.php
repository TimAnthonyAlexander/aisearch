<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\Search;

use TimAlexander\Aisearch\SystemConfig\SystemConfig;

class Search
{
    public function __construct(
        private SystemConfig $systemConfig
    ) {
    }
}
