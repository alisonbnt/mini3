<?php

namespace Mini\Core;


class Request{

    private $valid = true;
    private $modules = array();
    private $controller = null;
    private $action = null;
    private $parameters = array();

    public function makeInvalid(){
        $this->valid = false;
    }

    public function isValid(){
        return $this->valid;
    }

    public function addModule($moduleName){
        $this->modules[] = $moduleName;
    }

    public function getModules(){
        return $this->modules;
    }

    public function setController($controller){
        $this->controller = $controller;
    }

    public function getController(){
        return $this->controller;
    }

    public function setAction($action){
        $this->action = $action;
    }

    public function getAction(){
        return $this->action;
    }

    public function addParameter($parameter){
        $this->parameters[] = $parameter;
    }

    public function getParameters(){
        return $this->parameters;
    }

    public function hasModules(){
        return count($this->modules);
    }

    public function hasController(){
        return $this->controller != null;
    }

    public function hasAction(){
        return $this->action != null;
    }

    public function getControllerClass(){
        if(count($this->modules) > 0){
            $modulesNamespace = implode('\\', $this->modules) . '\\';
        }else{
            $modulesNamespace = "";
        }

        return '\\Mini\\Controller\\' . $modulesNamespace . $this->controller . 'Controller';
    }

}
