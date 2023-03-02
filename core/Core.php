<?php

namespace core;

use Exception;

class Core {
    private static $instance;

    public DatabaseContext $context;
    public $config;
    public $route;
    public $content;

    private function __construct(){}

    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public static function getHtml($viewPath, $params){
        if (!is_file($viewPath)) {
            $viewPath = "views/errors/404.php";
            $params = array("message" => "View not found");
        }
        ob_start();
        extract($params);
        include($viewPath);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function initialize(){
        $this->config = parse_ini_file("config/config.ini");

        $this->context = new DatabaseContext(
            $this->config["db_host"],
            $this->config["db_name"],
            $this->config["db_user"],
            $this->config["db_password"]
        );
    }
    public function run(){
        $route = trim($_SERVER["REQUEST_URI"], "/");
        $routeParts = explode("/", $route);

        $controllerName = array_shift($routeParts); 
        $actionName = array_shift($routeParts); 

        
        if (empty($controllerName)) {
            $controllerName = "main";
        }
        if (empty($actionName)) {
            $actionName = "index";
        }
        
        $controllerPath = "\\controllers\\".ucfirst($controllerName)."Controller"; 
        $actionPath = $actionName."Action"; 

        try {
            if (!class_exists($controllerPath)) {
                throw new Exception("Page not found", 404);
            }
            $controller = new $controllerPath();
            if (!method_exists($controller, $actionPath)) {
                throw new Exception("Page not found", 404);
            }

            $this->route["controllerName"] = $controllerName;
            $this->route["actionName"] = $actionName;
            $this->route["params"] = $routeParts;

            $this->content = $controller->$actionPath($routeParts);
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            $this->content = self::getHtml("views/errors/$code.php", [
                "message" => $message
            ]);
        }
    }
    public function done(){
        $theme = $this->config["app_theme"];
        $layoutPath = "themes/$theme/layout.php";
        echo self::getHtml($layoutPath, [
            "content" => $this->content
        ]);
    }
}