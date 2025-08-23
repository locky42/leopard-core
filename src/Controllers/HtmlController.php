<?php

namespace Leopard\Core\Controllers;

use Leopard\Core\View;

/**
 * Abstract class HtmlController
 *
 * Provides a base implementation for controllers that handle HTML rendering.
 * This class includes functionality for managing layouts, block paths, and views.
 *
 * @property string $layout The default layout file path used for rendering views.
 * @property string $blocksPath The directory path where block templates are located.
 * @property View $view The view instance used for rendering HTML content.
 */
abstract class HtmlController extends AbstractController
{
    /**
     * @var string The layout file path used for rendering views.
     * Default is 'layouts/main', which can be overridden in subclasses.
     */
    protected string $layout = 'layouts/main';

    /**
     * @var string The directory path where block templates are located.
     * Default is 'blocks', which can be overridden in subclasses.
     */
    protected string $blocksPath = 'blocks';

    /**
     * @var View The view instance used for rendering HTML content.
     * This is initialized in the constructor to point to the specific view directory.
     */
    protected View $view;

    /**
     * Constructor method for the HtmlController class.
     * Initializes the controller and sets up any necessary dependencies or configurations.
     */
    public function __construct()
    {
        parent::__construct();
        $this->view = new View($_SERVER['DOCUMENT_ROOT'] . '/../src/views/' . strtolower(explode('\\', static::class)[2]));
    }

    /**
     * Sets the layout for the HTML controller.
     *
     * @param string $layout The name of the layout to be set.
     * @return void
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Sets the path to the blocks directory.
     *
     * @param string $blocksPath The file system path to the blocks directory.
     * @return void
     */
    public function setBlocksPath(string $blocksPath): void
    {
        $this->blocksPath = $blocksPath;
    }
}
