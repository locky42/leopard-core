<?php

namespace Leopard\Core\Tests\Core;

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Leopard\Core\Router;
use Leopard\Core\Container;
use Leopard\Core\Tests\Controllers\TestController;
use Leopard\Core\Tests\Controllers\Test2Controller;

class RouterTest extends TestCase
{
    private Container $container;
    private Router $router;
    private Psr17Factory $psr17Factory;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->psr17Factory = new Psr17Factory();
        
        // Set up PSR-17 factory in container for API controllers
        $this->container->set('response', function () {
            return (new Psr17Factory())->createResponse();
        });
        
        // Make container available globally for controllers
        $GLOBALS['container'] = $this->container;
        
        $this->router = new Router($this->container);
        $this->router->registerController(TestController::class);
        $this->router->registerController(Test2Controller::class);
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

    public function testNotFoundRoute(): void
    {
        $request = new ServerRequest('GET', '/nonexistent/route');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('404 Not Found', (string) $response->getBody());
    }

    public function testRouteControllers()
    {
        $request = new ServerRequest('GET', '/test2/info');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from Test2Controller::info', (string) $response->getBody());
    }

    public function testRouteNamespace()
    {
        // Load namespace configuration
        $this->router->loadConfig([
            'controllers' => [
                [
                    'namespace' => 'TestApi',
                    'path' => '/api'
                ]
            ]
        ]);
        
        // StatusController with statusAction() -> /api/status/status
        $request = new ServerRequest('GET', '/api/status/status');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('API Status: OK', (string) $response->getBody());
    }

    public function testControllerWithPath()
    {
        // Load controller with specific path
        $this->router->loadConfig([
            'controllers' => [
                [
                    'controller' => 'ToolsController',
                    'path' => '/tools'
                ]
            ]
        ]);
        
        // Register the controller
        $this->router->registerController(\Leopard\Core\Tests\Controllers\ToolsController::class);
        
        // Test /tools/hash -> hashAction()
        $request = new ServerRequest('GET', '/tools/hash');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hash tool page', (string) $response->getBody());
        
        // Test /tools/profile with GET prefix -> getProfileAction()
        $request = new ServerRequest('GET', '/tools/profile');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('GET Profile tool', (string) $response->getBody());
        
        // Test /tools/submit with POST prefix -> postSubmitAction()
        $request = new ServerRequest('POST', '/tools/submit');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('POST Submit tool', (string) $response->getBody());
        
        // Test /tools -> indexAction()
        $request = new ServerRequest('GET', '/tools');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Tools index', (string) $response->getBody());
        
        // Test that helperMethod is NOT routed (no Action suffix)
        $request = new ServerRequest('GET', '/tools/helpermethod');
        $response = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        $this->assertEquals(404, $response->getStatusCode());
    }
}
