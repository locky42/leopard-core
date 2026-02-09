<?php

namespace Leopard\Core;

use Leopard\Core\Attributes\Route as RouteAttribute;
use ReflectionClass;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Router
 * 
 * The Router class is responsible for managing application routes and dispatching requests
 * to the appropriate controllers and actions. It supports loading routes from YAML configuration
 * files, dynamically discovering controllers, and handling route attributes.
 * 
 * @property Container $container Dependency injection container for resolving controllers.
 * @property array $routes List of registered routes with their metadata.
 * @property array $yamlRoutes Routes loaded from YAML configuration.
 * @property array $yamlControllers Controllers and their base paths loaded from YAML configuration.
 */
class Router
{
    /**
     * @var Container The dependency injection container instance.
     */
    private Container $container;

    /**
     * @var array The list of registered routes.
     * Each route is an associative array with keys: 'method', 'path', 'regex', 'params', 'controller', and 'action'.
     */
    private array $routes = [];

    /**
     * @var array The routes loaded from YAML configuration.
     * Each entry is keyed by 'App\Controllers\ControllerName::actionName' and contains 'method' and 'path'.
     */
    private array $yamlRoutes = [];

    /**
     * @var array The controllers loaded from YAML configuration.
     * Each entry is keyed by the fully qualified controller class name and contains the base path.
     */
    private array $yamlControllers = [];

    /**
     * Router constructor.
     *
     * Initializes the Router with a dependency injection container.
     *
     * @param Container $container The dependency injection container instance.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Loads routes from a YAML configuration file and stores them in the router.
     *
     * This method parses the specified YAML file to extract route definitions.
     * Each route is expected to have the following structure:
     * - `controller`: The name of the controller class (without namespace).
     * - `action`: The method in the controller to handle the route.
     * - `method`: The HTTP method (e.g., GET, POST, etc.).
     * - `path`: The URL path for the route.
     *
     * The routes are stored in the `$yamlRoutes` property with the key format:
     * `App\Controllers\ControllerName::actionName`.
     *
     * @param string $yamlPath The path to the YAML configuration file.
     * @return void
     * @throws \Symfony\Component\Yaml\Exception\ParseException If the YAML file cannot be parsed.
     */
    public function loadConfig(array $config): void
    {
        // Explicit routes
        if (!empty($config['routes'])) {
            foreach ($config['routes'] as $route) {
                $controller = 'App\\Controllers\\' . $route['controller'];
                $key = $controller . '::' . $route['action'];
                $this->yamlRoutes[$key] = [
                    'method' => strtoupper($route['method']),
                    'path' => $route['path'],
                ];
            }
        }

        // Basic controller paths
        if (!empty($config['controllers'])) {
            foreach ($config['controllers'] as $entry) {
                if (isset($entry['namespace'])) {
                    $this->loadNamespaceControllers($entry['namespace'], $entry['path']);
                } else {
                    $controller = 'App\\Controllers\\' . $entry['controller'];
                    $this->yamlControllers[$controller] = $entry['path'] ?? null;
                }
            }
        }
    }

    /**
     * Loads and registers controllers from a specified namespace and base path.
     *
     * This method scans the directory corresponding to the given namespace for PHP files
     * ending with "Controller.php". It converts the file paths to class names, checks if
     * the classes exist, and registers them as controllers.
     *
     * @param string $namespace The namespace to load controllers from.
     * @param string $basePath The base path associated with the controllers.
     *
     * @return void
     */
    private function loadNamespaceControllers(string $namespace, string $basePath): void
    {
        // Determine the base directory - use DOCUMENT_ROOT if available, otherwise use current working directory
        $baseDir = $_SERVER['DOCUMENT_ROOT'] ?? getcwd();
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/..';
        } else {
            // During tests, use the project root
            $baseDir = dirname(__DIR__, 4); // Go up from vendor/locky42/leopard-core/src to project root
        }
        
        // Try multiple possible base directories for namespace controllers
        $possibleDirs = [
            // Application controllers
            $baseDir . '/src/Controllers/' . $namespace,
            // Test controllers (leopard-core framework tests)
            $baseDir . '/vendor/locky42/leopard-core/tests/Controllers/' . $namespace,
        ];

        foreach ($possibleDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), 'Controller.php')) {
                    $class = $this->convertPathToClass($file->getPathname());
                    
                    if (class_exists($class)) {
                        $this->yamlControllers[$class] = $basePath;
                        $this->registerController($class);
                    }
                }
            }
        }
    }

    /**
     * Loads and registers controller classes from the specified directory.
     *
     * This method scans the given directory recursively for PHP files
     * that end with "Controller.php". For each matching file, it attempts
     * to convert the file path to a fully qualified class name and checks
     * if the class exists. If the class exists, it registers the controller.
     * Otherwise, it throws a RuntimeException indicating the missing class.
     *
     * @param string $dir The directory to scan for controller files.
     * 
     * @throws \RuntimeException If a controller class is not found in a file.
     */
    public function loadControllersFrom(string $dir): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Controller.php')) {
                $class = $this->convertPathToClass($file->getPathname());
                if (class_exists($class)) {
                    $this->registerController($class);
                } else {
                    throw new \RuntimeException("Controller class $class not found in file: " . $file->getPathname());
                }
            }
        }
    }

    /**
     * Converts a file path to a fully qualified class name.
     *
     * This method takes a file path, removes the base directory and file extension,
     * and transforms the remaining path into a namespace-compatible format.
     * Supports both application controllers (App\Controllers) and test controllers
     * (Leopard\Core\Tests\Controllers).
     *
     * @param string $path The absolute file path to be converted.
     * @return string The fully qualified class name corresponding to the given file path.
     */
    private function convertPathToClass(string $path): string
    {
        // Determine the base directory
        $baseDir = $_SERVER['DOCUMENT_ROOT'] ?? getcwd();
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/..';
        } else {
            // During tests, use the project root
            $baseDir = dirname(__DIR__, 4);
        }
        
        // Try to match test controllers path first
        $testPath = $baseDir . '/vendor/locky42/leopard-core/tests/Controllers/';
        if (str_starts_with($path, $testPath)) {
            $relative = str_replace([$testPath, '.php'], '', $path);
            $namespace = str_replace('/', '\\', $relative);
            return 'Leopard\\Core\\Tests\\Controllers\\' . $namespace;
        }
        
        // Otherwise, assume application controller
        $appPath = $baseDir . '/src/Controllers/';
        $relative = str_replace([$appPath, '.php'], '', $path);
        $namespace = str_replace('/', '\\', $relative);
        return 'App\\Controllers\\' . $namespace;
    }

    /**
     * Registers a controller and its public methods as routes in the router.
     *
     * This method analyzes the provided controller class and its public methods
     * to determine the routes associated with them. Routes can be defined using
     * attributes, YAML configuration, or inferred from the controller's namespace
     * and method names.
     *
     * @param string $controllerClass The fully qualified class name of the controller.
     *
     * The method performs the following steps:
     * 1. Checks for the #[Route] attribute on each public method to define the route path and method.
     * 2. If no attribute is found, it checks the YAML configuration for routes associated with the controller and method.
     * 3. If neither attributes nor YAML routes are defined, it infers the route path based on the controller's namespace
     *    and method names. Special handling is applied for methods named `index()`.
     *
     * The resulting route is compiled into a regex pattern and stored in the router's route list.
     *
     * Example route structure added to `$this->routes`:
     * - 'method': HTTP method (e.g., GET, POST)
     * - 'path': Route path (e.g., /example)
     * - 'regex': Compiled regex for matching the route
     * - 'params': Parameters extracted from the route path
     * - 'controller': Controller class name
     * - 'action': Method name within the controller
     *
     * @throws ReflectionException If the controller class does not exist or cannot be reflected.
     */
    public function registerController(string $controllerClass): void
    {
        $refClass = new ReflectionClass($controllerClass);

        foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) continue;

            $methodName = $method->getName();
            $routePath = null;
            $routeMethod = 'GET';

            // 1. Attribute #[Route]
            foreach ($method->getAttributes(RouteAttribute::class) as $attr) {
                /** @var RouteAttribute $route */
                $route = $attr->newInstance();
                $routePath = $route->path;
                $routeMethod = strtoupper($route->method);
                break;
            }

            // 2. YAML routes:
            if (!$routePath) {
                $key = $controllerClass . '::' . $methodName;
                if (isset($this->yamlRoutes[$key])) {
                    $routePath = $this->yamlRoutes[$key]['path'];
                    $routeMethod = $this->yamlRoutes[$key]['method'];
                }
            }

            // 3. YAML controllers:
            if (!$routePath) {
                // Only process methods ending with 'Action' for auto-routing
                if (!str_ends_with($methodName, 'Action')) {
                    continue;
                }
                
                // Extract HTTP method from prefix and action name
                $httpMethods = ['get', 'post', 'put', 'delete', 'patch', 'options', 'head'];
                $actionName = $methodName;
                
                foreach ($httpMethods as $httpMethod) {
                    if (stripos($methodName, $httpMethod) === 0) {
                        $routeMethod = strtoupper($httpMethod);
                        // Remove the HTTP method prefix and 'Action' suffix to get the action name
                        $actionName = substr($methodName, strlen($httpMethod), -6); // -6 for 'Action'
                        break;
                    }
                }
                
                // If no HTTP method prefix found, just remove 'Action' suffix
                if ($actionName === $methodName) {
                    $actionName = substr($methodName, 0, -6);
                }
                
                // Convert empty action name or 'index' to special case
                if ($actionName === '' || strtolower($actionName) === 'index') {
                    $actionName = 'index';
                } else {
                    $actionName = $this->actionNameToPath($actionName);
                }
                
                if (array_key_exists($controllerClass, $this->yamlControllers)) {
                    $basePath = $this->yamlControllers[$controllerClass];
                    
                    // If basePath is null, generate from namespace
                    if ($basePath === null) {
                        $basePath = $this->namespaceToPath($controllerClass);
                    }
                    
                    // If basePath is '/', we need to include controller name in the path
                    if ($basePath === '/') {
                        // Extract just the controller name (without namespace parts)
                        $parts = explode('\\', $controllerClass);
                        $controllerName = end($parts);
                        $controllerName = strtolower(preg_replace('/Controller$/', '', $controllerName));
                        
                        if (strtolower($actionName) === 'index') {
                            $routePath = '/' . $controllerName;
                        } else {
                            $routePath = '/' . $controllerName . '/' . $actionName;
                        }
                    }
                    // If basePath is empty string, use root
                    elseif ($basePath === '') {
                        if (strtolower($actionName) === 'index') {
                            $routePath = '/';
                        } else {
                            $routePath = '/' . $actionName;
                        }
                    } else {
                        // Use the specified basePath + controller name
                        $parts = explode('\\', $controllerClass);
                        $controllerName = end($parts);
                        $controllerName = strtolower(preg_replace('/Controller$/', '', $controllerName));
                        
                        if (strtolower($actionName) === 'index') {
                            $routePath = rtrim($basePath, '/') . '/' . $controllerName;
                        } else {
                            $routePath = rtrim($basePath, '/') . '/' . $controllerName . '/' . $actionName;
                        }
                    }
                } else {
                    // Not in YAML controllers, generate from namespace
                    $basePath = $this->namespaceToPath($controllerClass);
                    if (strtolower($actionName) === 'index') {
                        $routePath = $basePath;
                    } else {
                        $routePath = rtrim($basePath, '/') . '/' . $actionName;
                    }
                }
            }

            list($regex, $params) = $this->compilePath($routePath);

            $this->routes[] = [
                'method' => $routeMethod,
                'path' => $routePath,
                'regex' => $regex,
                'params' => $params,
                'controller' => $controllerClass,
                'action' => $methodName,
            ];
        }
    }

    /**
     * Converts a fully qualified class name to a lowercase path string suitable for routing.
     *
     * The method tries to remove common prefixes ("App\Controllers\" or ends with "\Controllers\"),
     * splits the remaining namespace into segments, converts each segment to lowercase,
     * and removes the "Controller" suffix from the last segment.
     *
     * @param string $class The fully qualified class name to be converted.
     * @return string The resulting path string, starting with a forward slash.
     */
    private function namespaceToPath(string $class): string
    {
        // Try to remove App\Controllers\ prefix
        $trimmed = str_replace('App\\Controllers\\', '', $class);
        
        // If it didn't change, try to find and remove any \Controllers\ segment
        if ($trimmed === $class) {
            // Find the last occurrence of \Controllers\ and take everything after it
            $pos = strrpos($class, '\\Controllers\\');
            if ($pos !== false) {
                $trimmed = substr($class, $pos + strlen('\\Controllers\\'));
            } else {
                // No Controllers namespace found, just use the class name
                $parts = explode('\\', $class);
                $trimmed = end($parts);
            }
        }
        
        $segments = explode('\\', $trimmed);
        $segments = array_map(fn($s) => strtolower(preg_replace('/Controller$/', '', $s)), $segments);
        return '/' . implode('/', $segments);
    }

    /**
     * Converts action method names to URL path segments.
     * Example: base64Encode -> base64-encode, foo_bar -> foo-bar
     */
    private function actionNameToPath(string $actionName): string
    {
        $withHyphens = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $actionName);
        $withHyphens = str_replace('_', '-', $withHyphens);
        return strtolower($withHyphens);
    }

    /**
     * Dispatches a request to the appropriate route and controller action.
     *
     * This method matches the provided HTTP method and URI against the registered routes.
     * If a match is found, it invokes the corresponding controller action with the extracted
     * parameters and returns the response. If no match is found, it returns a 404 error response.
     *
     * @param string $method The HTTP method of the request (e.g., GET, POST).
     * @param string $uri The URI of the request.
     * 
     * @return ResponseInterface The PSR-7 response object.
     *
     * @throws \ReflectionException If the controller method cannot be reflected.
     */
    public function dispatch(string $method, string $uri): ResponseInterface
    {
        $uri = rtrim($uri, '/') ?: '/';
        $psr17Factory = new Psr17Factory();

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                if (!(strtoupper($method) === 'HEAD' && $route['method'] === 'GET')) {
                    continue;
                }
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                array_shift($matches); // Remove full match

                $params = [];
                foreach ($route['params'] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }

                $controller = $this->container->get($route['controller']);
                $refMethod = new \ReflectionMethod($controller, $route['action']);
                $args = [];
                foreach ($refMethod->getParameters() as $param) {
                    $name = $param->getName();
                    $type = $param->getType()?->getName();

                    if (!array_key_exists($name, $params)) {
                        $args[] = null;
                        continue;
                    }

                    $value = $params[$name];

                    // Type conversion
                    if ($type === 'int') {
                        if (!ctype_digit($value)) {
                            return $this->createErrorResponse($psr17Factory, 404, "404 Not Found (invalid int: $name)");
                        }
                        $value = (int) $value;
                    } elseif ($type === 'float') {
                        if (!is_numeric($value)) {
                            return $this->createErrorResponse($psr17Factory, 404, "404 Not Found (invalid float: $name)");
                        }
                        $value = (float) $value;
                    } elseif ($type === 'bool') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($value === null) {
                            return $this->createErrorResponse($psr17Factory, 404, "404 Not Found (invalid bool: $name)");
                        }
                    } elseif ($type !== null && $type !== 'string') {
                        return $this->createErrorResponse($psr17Factory, 500, "Unsupported parameter type: $type");
                    }

                    $args[] = $value;
                }

                $response = $psr17Factory->createResponse(200);
                $this->container->set('response', function () use ($response) {
                    return $response;
                });

                $responseBody = $refMethod->invokeArgs($controller, $args);
                
                // Ensure the response body is a string
                $responseBody = $responseBody ?? ''; // Default to an empty string if null
                
                // Get the potentially updated response from the container
                $finalResponse = $this->container->get('response');
                $finalResponse->getBody()->write((string)$responseBody);
                return $finalResponse;
            }
        }

        return $this->createErrorResponse($psr17Factory, 404, "404 Not Found");
    }

    /**
     * Retrieves the list of registered routes.
     *
     * @return array The array of registered routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /** 
     * Finds a route by its controller class and action name.
     */
    public function getRoute(string $controllerClass, string $actionName, bool $onlyPath = false): array|string|null
    {
        $filtered = array_filter(
            $this->routes,
            fn($route) => $route['controller'] === $controllerClass && $route['action'] === $actionName
        );

        if (!$filtered) {
            return null;
        }

        $route = array_shift($filtered);

        return $onlyPath ? $route['path'] : $route;
    }

    /**
     * Creates a PSR-7 response with an error message.
     *
     * This method creates a response with the specified status code and message.
     * It uses the Psr17Factory to create the response object and writes the message
     * to the response body.
     *
     * @param Psr17Factory $factory The PSR-17 factory for creating responses.
     * @param int $statusCode The HTTP status code for the response.
     * @param string $message The error message to include in the response body.
     * @return ResponseInterface The created PSR-7 response object.
     */
    private function createErrorResponse(Psr17Factory $factory, int $statusCode, string $message): ResponseInterface
    {
        $response = $factory->createResponse($statusCode);
        $this->container->set('response', function () use ($response) {
            return $response;
        });
        $response->getBody()->write($message);
        return $response;
    }

    /**
     * Compiles a route path into a regular expression and extracts parameter names.
     *
     * This method takes a route path containing placeholders in the format `{name}`
     * and converts it into a regular expression that can be used to match URLs.
     * It also extracts the names of the placeholders for later use.
     *
     * @param string $path The route path containing placeholders (e.g., "/user/{id}/profile").
     * @return array An array containing:
     *               - The compiled regular expression as a string.
     *               - An array of parameter names extracted from the placeholders.
     */
    private function compilePath(string $path): array
    {
        $paramNames = [];
        $regex = preg_replace_callback('#\{([^}]+)\}#', function ($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([^/]+)';
        }, $path);
        
        $regex = '#^' . $regex . '$#';
        return [$regex, $paramNames];
    }
}
