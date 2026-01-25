<?php

namespace Leopard\Core\Tests\Controllers;

class ToolsController
{
    public function hashAction(): string
    {
        return "Hash tool page";
    }
    
    public function getProfileAction(): string
    {
        return "GET Profile tool";
    }
    
    public function postSubmitAction(): string
    {
        return "POST Submit tool";
    }
    
    public function indexAction(): string
    {
        return "Tools index";
    }
    
    // This should NOT be registered as route (no Action suffix)
    public function helperMethod(): string
    {
        return "Helper method";
    }
}
