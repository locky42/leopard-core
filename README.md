# Leopard Core

`leopard-core` is the core library for the Leopard Framework, providing essential features such as routing, attributes, dependency injection container, and other foundational components.

---

## Table of Contents

- [Installation](#installation)
- [Core Components](#core-components)
  - [Dependency Injection Container](#dependency-injection-container)
  - [Routing](#routing)
  - [Attributes](#attributes)
  - [ContractFactory](#contractfactory)
  - [View](#view)
  - [SEO](#seo)
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

The `Router` allows you to define routes using attributes, YAML configuration, and automatic route generation based on controller structure.

#### Routing Methods

1. **Attribute-based routing** - Define routes using PHP attributes
2. **YAML configuration** - Define routes and controller paths in YAML
3. **Auto-routing** - Automatic route generation for methods ending with `Action` suffix

#### Action Method Convention

For auto-routing (YAML controllers), only methods ending with `Action` suffix are processed as routes:

- **HTTP method prefix**: `get`, `post`, `put`, `delete`, `patch`, `options`, `head`
- **Default method**: If no prefix is specified, `GET` is used by default
- **Action name**: Formed by removing the HTTP method prefix (if any) and the `Action` suffix

**Examples:**

```php
class UserController
{
    // GET /user/about
    public function aboutAction(): string
    {
        return "About page";
    }
    
    // GET /user/profile
    public function getProfileAction(): string
    {
        return "User profile (GET)";
    }
    
    // POST /user/profile
    public function postProfileAction(): string
    {
        return "Update profile (POST)";
    }
    
    // DELETE /user/account
    public function deleteAccountAction(): string
    {
        return "Delete account";
    }
    
    // GET /user (index is special case)
    public function indexAction(): string
    {
        return "User index";
    }
    
    // This method will NOT be routed (no Action suffix)
    public function helperMethod(): string
    {
        return "Not a route";
    }
}
```

#### Attribute-based Routing Example:

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
    
    #[Route('/user/{id}', method: 'GET')]
    public function getUser(string $id): string
    {
        return "User ID: $id";
    }
}

$router = new Router($container);
$router->registerController(TestController::class);
$response = $router->dispatch('GET', '/test');
```

#### YAML Configuration Example:

```yaml
routes:
  - controller: User/ProfileController
    action: show
    method: GET
    path: /profile/{id}

controllers:
  - controller: Site/PageController
    path: /pages
    
  - namespace: Api
    path: /api
```

#### YAML Controllers Behavior

- `controllers[].controller` registers one конкретний контролер (relative to `App\\Controllers\\...`, or absolute FQCN if it starts with `\\`).
- `controllers[].namespace` scans all `*Controller.php` in `src/Controllers/{Namespace}` and registers them with the same base path.
- Only methods ending with `Action` are auto-routed from YAML controller definitions.
- HTTP method prefix is detected from method name: `get|post|put|delete|patch|options|head`.
- Special path handling for `path`:
    - `path: /` → `/{controller}` and `/{controller}/{action}`
    - `path: ""` → `/` and `/{action}`
    - `path: /base` → `/base/{controller}` and `/base/{controller}/{action}`

#### Dynamic Parameters in Paths

The router supports these placeholders in route paths:

- `{id}` - one URI segment (no slash)
- `{id:\\d+}` - custom regex constraint
- `{path}` - greedy capture including `/`

If a parameter type in controller method is `int|float|bool` and conversion fails, router returns `404`.

`HEAD` requests are allowed to match `GET` routes.

#### Auto-routing with loadControllersFrom:

```php
$router = new Router($container);

// Load all controllers from directory
$router->loadControllersFrom(__DIR__ . '/src/Controllers');

// Methods with Action suffix will be auto-registered
// GET /test/about -> TestController::aboutAction()
// POST /test/submit -> TestController::postSubmitAction()
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

### ContractFactory

The `ContractFactory` is a universal factory for creating instances through interface contracts. It enables flexible dependency management by allowing you to register and swap implementations without modifying existing code.

#### Key Benefits:
- **Flexibility** - Easily swap implementations
- **Testability** - Create mock objects for testing
- **Extensibility** - Add custom implementations
- **Dependency Inversion** - Depend on abstractions, not concrete classes

#### Basic Usage:

```php
use Leopard\Core\Factory\ContractFactory;

// Define an interface
interface LoggerInterface {
    public function log(string $message): void;
}

// Create an implementation
class FileLogger implements LoggerInterface {
    public function log(string $message): void {
        file_put_contents('app.log', $message . PHP_EOL, FILE_APPEND);
    }
}

// Register the implementation
ContractFactory::register(LoggerInterface::class, FileLogger::class);

// Create instances through the factory
$logger = ContractFactory::create(LoggerInterface::class);
$logger->log('Application started');
```

#### Swapping Implementations:

```php
// Production logger
class ProductionLogger implements LoggerInterface {
    public function log(string $message): void {
        // Send to external service
    }
}

// Test logger
class TestLogger implements LoggerInterface {
    private array $logs = [];
    
    public function log(string $message): void {
        $this->logs[] = $message;
    }
    
    public function getLogs(): array {
        return $this->logs;
    }
}

// In production
ContractFactory::register(LoggerInterface::class, ProductionLogger::class);

// In tests
ContractFactory::register(LoggerInterface::class, TestLogger::class);
```

#### Available Methods:

- `register(string $interface, string $className, array $doctrineMapping = []): void` - Register an implementation (and sync Doctrine mapping when available)
- `create(string $interface): object` - Create an instance
- `getMapping(string $interface): ?string` - Get registered class name
- `hasMapping(string $interface): bool` - Check if interface is registered
- `getMappings(): array` - Get all registered mappings
- `unregister(string $interface): bool` - Unregister an interface
- `clear(): void` - Clear all mappings
- `reset(): void` - Reset to initial state

#### Integration Example:

```php
use Leopard\Core\Factory\ContractFactory;
use Leopard\User\Contracts\Models\UserInterface;
use App\Models\User;

// Register user models
ContractFactory::register(UserInterface::class, User::class);

// Use in your application
class UserService {
    public function createUser(array $data): UserInterface {
        $user = ContractFactory::create(UserInterface::class);
        $user->setPassword($data['password']);
        return $user;
    }
}
```

#### Doctrine Integration (auto ResolveTargetEntity)

If `locky42/leopard-doctrine` is installed, `ContractFactory::register(...)` automatically forwards the mapping to `ResolveTargetEntityRegistry::addResolveTargetEntity(...)`.

```php
use Leopard\Core\Factory\ContractFactory;
use Leopard\User\Contracts\Models\UserInterface;
use App\Models\User;

// Registers ContractFactory mapping
// + auto-registers Doctrine resolve-target mapping
ContractFactory::register(UserInterface::class, User::class);
```

You can also pass Doctrine mapping options as the third argument:

```php
ContractFactory::register(
    UserInterface::class,
    User::class,
    ['fetch' => 'EAGER']
);
```

In application projects, keep all contract mappings in one file (for example `config/contract-mappings.php`) and include it in bootstrap.

#### Best Practices:

1. **Always use `::class` syntax:**
   ```php
   // Good
   ContractFactory::register(UserInterface::class, User::class);
   
   // Bad
   ContractFactory::register('UserInterface', 'User');
   ```

2. **Register at application bootstrap:**
   ```php
    // config/contract-mappings.php
   ContractFactory::register(UserInterface::class, User::class);
   ContractFactory::register(LoggerInterface::class, FileLogger::class);

    // bootstrap.php
    require_once __DIR__ . '/config/contract-mappings.php';
   ```

3. **Use type hints with interfaces:**
   ```php
   // Good - flexible
   public function processUser(UserInterface $user) { }
   
   // Bad - tightly coupled
   public function processUser(User $user) { }
   ```

4. **Clear mappings in tests:**
   ```php
   class MyTest extends TestCase {
       protected function setUp(): void {
           ContractFactory::clear();
           ContractFactory::register(UserInterface::class, MockUser::class);
       }
       
       protected function tearDown(): void {
           ContractFactory::reset();
       }
   }
   ```

#### Error Handling:

The factory throws `InvalidArgumentException` in these cases:
- Class doesn't exist
- Interface doesn't exist
- Class doesn't implement the interface
- No mapping found when creating instance

```php
try {
    $user = ContractFactory::create(UserInterface::class);
} catch (\InvalidArgumentException $e) {
    // Handle: interface not registered
    echo "Error: " . $e->getMessage();
}
```

---

### View

The `View` class is responsible for rendering templates and managing the presentation layer. It supports layouts, blocks, and integration with the SEO service.

#### Features:
- **Template rendering** with data passing
- **Layout system** for consistent page structure
- **Block rendering** for reusable components
- **CSS and JavaScript management**
- **SEO metadata** through integrated Seo service

#### Example:

```php
use Leopard\Core\View;

$view = new View(__DIR__ . '/src/views');

// Set custom layout
$view->setLayout('layouts/admin');

// Add styles and scripts
$view->addStyle('/assets/css/main.css');
$view->addScript('/assets/js/app.js');

// Configure SEO
$view->getSeo()->setTitle('Welcome Page');
$view->getSeo()->setDescription('This is the homepage');
$view->getSeo()->setKeywords(['php', 'framework', 'leopard']);

// Render view
echo $view->render('site/home', [
    'username' => 'John',
    'data' => ['foo' => 'bar']
]);
```

#### Rendering Blocks:

```php
// In your layout file (layouts/main.php)
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->getSeo()->getTitle() ?></title>
</head>
<body>
    <?= $this->renderBlock('header') ?>
    
    <main><?= $content ?></main>
    
    <?= $this->renderBlock('footer') ?>
</body>
</html>
```

---

### SEO

The `Seo` service manages SEO metadata for your pages, including meta tags, Open Graph, Twitter Cards, and more.

#### Features:
- **Meta tags** management
- **Open Graph** tags for social media
- **Twitter Cards** support
- **Canonical URLs**
- **Keywords** management
- **Robots** directives
- **Charset** configuration

#### Example:

```php
use Leopard\Core\Services\Seo;

$seo = new Seo();

// Basic SEO
$seo->setTitle('My Awesome Page');
$seo->setDescription('A detailed description of my page');
$seo->setCanonicalUrl('https://example.com/page');
$seo->setKeywords(['keyword1', 'keyword2', 'keyword3']);
$seo->setRobots('index, follow');
$seo->setCharset('UTF-8');

// Add custom meta tags
$seo->addMetaTag('author', 'John Doe');
$seo->addMetaTag('viewport', 'width=device-width, initial-scale=1.0');

// Open Graph tags
$seo->addOpenGraphTag('og:title', 'My Awesome Page');
$seo->addOpenGraphTag('og:type', 'website');
$seo->addOpenGraphTag('og:url', 'https://example.com/page');
$seo->addOpenGraphTag('og:image', 'https://example.com/image.jpg');

// Twitter Cards
$seo->addTwitterCard('twitter:card', 'summary_large_image');
$seo->addTwitterCard('twitter:title', 'My Awesome Page');
$seo->addTwitterCard('twitter:description', 'A detailed description');

// Access in templates
echo $seo->getTitle(); // "My Awesome Page"
echo implode(', ', $seo->getKeywords()); // "keyword1, keyword2, keyword3"
```

#### Rendering SEO Tags in Layout:

```php
// In your layout file
<head>
    <meta charset="<?= $this->getSeo()->getCharset() ?? 'UTF-8' ?>">
    <title><?= htmlspecialchars($this->getSeo()->getTitle() ?? 'Default Title') ?></title>
    <meta name="description" content="<?= htmlspecialchars($this->getSeo()->getDescription() ?? '') ?>">
    
    <?php if ($this->getSeo()->getCanonicalUrl()): ?>
        <link rel="canonical" href="<?= htmlspecialchars($this->getSeo()->getCanonicalUrl()) ?>">
    <?php endif; ?>
    
    <?php if ($this->getSeo()->getRobots()): ?>
        <meta name="robots" content="<?= htmlspecialchars($this->getSeo()->getRobots()) ?>">
    <?php endif; ?>
    
    <?php if ($this->getSeo()->getKeywords()): ?>
        <meta name="keywords" content="<?= htmlspecialchars(implode(', ', $this->getSeo()->getKeywords())) ?>">
    <?php endif; ?>
    
    <?php foreach ($this->getSeo()->getMetaTags() as $name => $content): ?>
        <meta name="<?= htmlspecialchars($name) ?>" content="<?= htmlspecialchars($content) ?>">
    <?php endforeach; ?>
    
    <?php foreach ($this->getSeo()->getOpenGraphTags() as $property => $content): ?>
        <meta property="<?= htmlspecialchars($property) ?>" content="<?= htmlspecialchars($content) ?>">
    <?php endforeach; ?>
    
    <?php foreach ($this->getSeo()->getTwitterCards() as $name => $content): ?>
        <meta name="<?= htmlspecialchars($name) ?>" content="<?= htmlspecialchars($content) ?>">
    <?php endforeach; ?>
</head>
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
