<?php

namespace App\Attributes;

use Attribute;

/**
 * Attribute to define API route documentation in a cleaner way
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ApiRoute
{
    public function __construct(
        public string $method,
        public string $path,
        public string $summary,
        public string $description = '',
        public array $tags = [],
        public bool $requiresAuth = true,
        public array $parameters = [],
        public array $requestBody = [],
        public array $responses = []
    ) {
    }
}

/**
 * Attribute for API request body documentation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ApiRequestBody
{
    public function __construct(
        public array $required = [],
        public array $properties = [],
        public bool $isRequired = true
    ) {
    }
}

/**
 * Attribute for API response documentation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ApiResponse
{
    public function __construct(
        public int $code,
        public string $description,
        public array $schema = []
    ) {
    }
}
