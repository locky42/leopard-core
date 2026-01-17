<?php

namespace Leopard\Core\Events;

class AfterViewInit {
    public function __construct(public ?\Leopard\Core\View $view = null) {}
}
