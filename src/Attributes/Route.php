<?php

namespace App\Core\Attributes;

use Attribute;

/**
 * Route attribute to define a route for a controller method.
 *
 * @package App\Core\Attributes
 * @Annotation
 * @Target("METHOD")
 * @Attributes({
 *   @Attribute("path", type = "string"),
 *   @Attribute("method", type = "string", default = "GET")
 * })
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET'
    ) {}
}
