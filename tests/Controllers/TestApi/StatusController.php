<?php

namespace Leopard\Core\Tests\Controllers\TestApi;

use Leopard\Core\Controllers\ApiController;

class StatusController extends ApiController
{
    public function statusAction()
    {
        return $this->formatResponse(['message' => 'API Status: OK']);
    }
}
