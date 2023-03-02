<?php

namespace controllers;

use core\Controller;

class MainController extends Controller{
    public function indexAction()
    {
        return $this->render([
            "title" => "Welcome to MVC",
            "subtitle" => "MVC - Model View Controller"
        ]);
    }
}