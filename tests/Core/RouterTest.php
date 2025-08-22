<?php

namespace Leopard\Core\Tests\Core;

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Leopard\Core\Router;
use Leopard\Core\Container;
use Leopard\Core\Tests\Controllers\TestController;

class RouterTest extends TestCase
{
    private Container $container;
    private Router $router;
    private Psr17Factory $psr17Factory;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->router = new Router($this->container);
        $this->psr17Factory = new Psr17Factory();
        $this->router->registerController(TestController::class);
    }

    public function testGetTestRoute(): void
    {
        $request = new ServerRequest('GET', '/test');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from TestController::test', (string) $response->getBody());
    }

    public function testPostDataRoute(): void
    {
                $request = new ServerRequest('POST', '/test/data');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Data received in TestController::postData', (string) $response->getBody());
    }

    public function testPutRoute(): void
    {
        $request = new ServerRequest('PUT', '/test/put');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from TestController::testPut', (string) $response->getBody());
    }

    public function testDeleteRoute(): void
    {
        $request = new ServerRequest('DELETE', '/test/delete');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from TestController::testDelete', (string) $response->getBody());
    }

    public function testOptionsRoute(): void
    {
        $request = new ServerRequest('OPTIONS', '/test/options');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from TestController::testOptions', (string) $response->getBody());
    }

    public function testHeadRoute(): void
    {
        $request = new ServerRequest('HEAD', '/test/head');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from TestController::testHead', (string) $response->getBody());
    }

    public function testPatchRoute(): void
    {
        $request = new ServerRequest('PATCH', '/test/patch');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from TestController::testPatch', (string) $response->getBody());
    }

    public function testGetUserRoute(): void
    {
        $request = new ServerRequest('GET', '/user/123');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('User ID: 123', (string) $response->getBody());
    }

    public function testGetPostCommentRoute(): void
    {
        $request = new ServerRequest('GET', '/post/45/comment/67');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Post ID: 45, Comment ID: 67', (string) $response->getBody());
    }

    public function testGetProductRoute(): void
    {
        $request = new ServerRequest('GET', '/product/electronics/89');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Category: electronics, Product ID: 89', (string) $response->getBody());
    }
}
