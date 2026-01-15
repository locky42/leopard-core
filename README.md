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
