# Leopard Core

`leopard-core` is the core library for the Leopard Framework, providing essential features such as routing, attributes, dependency injection container, and other foundational components.

---

## Table of Contents

- [Installation](#installation)
- [Core Components](#core-components)
  - [Dependency Injection Container](#dependency-injection-container)
  - [Routing](#routing)
  - [Attributes](#attributes)
- [Usage Examples](#usage-examples)
- [Testing](#testing)

---

## Installation

Install `leopard-core` using Composer:

```bash
composer require locky42/leopard-core
```

---

## Core Components

### Dependency Injection Container

The `Container` is a simple dependency injection container that allows you to register services and retrieve their instances.

#### Example:

```php
use Leopard\Core\Container;

$container = new Container();

// Register a service
$container->set('logger', function () {
    return new Logger();
});

// Retrieve the service
$logger = $container->get('logger');
```

---

### Routing

The `Router` allows you to define routes using attributes and handle HTTP requests.

#### Example:

```php
use Leopard\Core\Router;
use Leopard\Core\Attributes\Route;

class TestController
{
    #[Route('/test', method: 'GET')]
    public function test(): string
    {
        return "Hello, world!";
    }
}

$router = new Router();
$response = $router->dispatch('/test', 'GET');
echo $response; // Outputs "Hello, world!"
```

---

### Attributes

`leopard-core` supports PHP attributes for defining routes and other metadata.

#### Example:

```php
use Leopard\Core\Attributes\Route;

#[Route('/user/{id}', method: 'GET')]
public function getUser(string $id): string
{
    return "User ID: $id";
}
```

---

## Usage Examples

### Loading Configuration

```php
use Leopard\Core\Config;

$config = new Config();
$config->load(__DIR__ . '/config/app.yaml');

echo $config->get('database.host'); // Outputs 'localhost'
```

---

## Testing

To run tests, use the following command:

```bash
./run-tests.sh
```

The tests are located in the `vendor/locky42/leopard-core/tests` directory.

---

## License

This project is licensed under the [MIT License](LICENSE).
