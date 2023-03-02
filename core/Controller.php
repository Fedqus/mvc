<?php

namespace core;

abstract class Controller{
    abstract public function indexAction();
    
    public function render($params = [], $viewPath = null){
        if (empty($viewPath)) {
            $controllerName = Core::getInstance()->route["controllerName"];
            $actionName = Core::getInstance()->route["actionName"];

            $viewPath = "views/$controllerName/$actionName.php";
        }
        return Core::getHtml($viewPath, $params);
    }
}