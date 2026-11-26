<?php

namespace App\Search;

// Pas de readonly : le Form écrit dans les propriétés lors du handleRequest()
final class BookSearchCriteria
{
    public function __construct(
        public ?string $q = null,
        public ?string $author = null,
        public bool $available = false,
    ) {
    }
}
