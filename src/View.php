<?php

namespace Leopard\Core;

/**
 * The View class is responsible for handling the rendering of templates
 * and managing the presentation layer of the application.
 * 
 * It provides methods to load and render views, passing data to templates
 * and ensuring separation of concerns between the application logic and
 * the user interface.
 */
class View
{
    /**
     * @var string The path to the directory containing view files.
     */
    private string $viewsPath;

    /**
     * @var string The layout template to be used for rendering views.
     * Defaults to 'layouts/main'.
     */
    private string $layout = 'layouts/main';

    /**
     * @var string The path to the directory containing block templates.
     * Defaults to 'blocks'.
     */
    private string $blocksPath = 'blocks';

    /**
     * @var array An array of styles to be included in the view.
     */
    private array $styles = [];

    /**
     * @var array An array of scripts to be included in the view.
     */
    private array $scripts = [];

    /**
     * Constructor for the View class.
     *
     * @param string $viewsPath The path to the directory containing view files.
     */
    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/');
    }

    /**
     * Sets the layout for the view.
     *
     * @param string $layout The name of the layout to be used.
     * @return void
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Sets the path to the blocks directory.
     *
     * @param string $blocksPath The file path to the blocks directory.
     * @return void
     */
    public function setBlocksPath(string $blocksPath): void
    {
        $this->blocksPath = $blocksPath;
    }

    /**
     * Adds a CSS style to the view.
     *
     * @param string $style The path or identifier of the style to be added.
     * @return void
     */
    public function addStyle(string $style): void
    {
        $this->styles[] = $style;
    }

    /**
     * Adds a JavaScript script to the view.
     *
     * @param string $script The path or identifier of the script to be added.
     * @return void
     */
    public function addScript(string $script): void
    {
        $this->scripts[] = $script;
    }

    /**
     * Renders a template with the provided data and returns the resulting output as a string.
     *
     * @param string $template The path or identifier of the template to be rendered.
     * @param array $data An associative array of data to be passed to the template. Default is an empty array.
     * @return string The rendered output of the template.
     */
    public function render(string $template, array $data = []): string
    {
        $templatePath = $this->viewsPath . '/' . $template . '.php';

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: $templatePath");
        }

        // Extract data variables for use in the template
        extract($data);

        // Start output buffering for the content
        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        // Render the layout with the content
        $layoutPath = $this->viewsPath . '/' . $this->layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout not found: $layoutPath");
        }

        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }

    /**
     * Renders a specific block with the provided data.
     *
     * @param string $block The name of the block to render.
     * @param array $data An associative array of data to pass to the block. Defaults to an empty array.
     * @return string The rendered content of the block.
     */
    public function renderBlock(string $block, array $data = []): string
    {
        // Check if the block exists in the specific layout folder
        $layoutSpecificBlockPath = $this->viewsPath . '/' . $this->layout . '/' . $block . '.php';
        $defaultBlockPath = $this->viewsPath . '/' . $this->blocksPath . '/' . $block . '.php';

        $blockPath = file_exists($layoutSpecificBlockPath) ? $layoutSpecificBlockPath : $defaultBlockPath;

        if (!file_exists($blockPath)) {
            throw new \RuntimeException("Block not found: $blockPath");
        }

        extract($data);

        ob_start();
        include $blockPath;
        return ob_get_clean();
    }

    /**
     * Gets the array of added styles.
     *
     * @return array The array of added styles.
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * Gets the array of added scripts.
     *
     * @return array The array of added scripts.
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }
}
