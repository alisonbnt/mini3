<?php
/** For more info about namespaces plase @see http://php.net/manual/en/language.namespaces.importing.php */
namespace Mini\Core;


class Application{

    private $hasParams = false;
    private $urlParams = null;
    private $request = null;


    /**
    * The setup:
    * Parse URL values and setup Idiorm
    */
    public function __construct(){
        // create array with URL parts in $url
        $this->splitUrl();
        $this->initIdiorm();
        $this->setEnvironment();
    }

    /**
    * "Start" the application:
    * Create a Request and calls the according controller/method or the
    * fallback
    */
    public function run(){
        $this->request = new \Mini\Core\Request();
        foreach($this->urlParams as $param){
            $this->handleUrlParam(ucfirst($param), $param);
        }

        if(!$this->hasParams){
            $this->request->setController('Home');
            $this->request->setAction('index');
        }else{
            if(!$this->request->hasController()){
                $this->request->setController('Home');
            }

            if(!$this->request->hasAction()){
                $this->request->setAction('index');
            }
        }

        $controllerClass = $this->request->getControllerClass();
        if(!class_exists($controllerClass) || !$this->request->isValid()){
            header('location: ' . URL . 'error');
            exit();
        }
        $controller = new $controllerClass();

        call_user_func_array(
            array($controller, $this->request->getAction()),
            $this->request->getParameters()
        );
    }

    private function handleUrlParam($ucfparam, $param){
        if(!$this->request->hasController()){
            $allModules = $this->request->getModules();
            if(count($allModules) > 0){
                $modulesPath = implode('/', $allModules);
                $dirPath = $modulesPath . '/' . $ucfparam;
            }else{
                $dirPath = $ucfparam;
            }

            $paramPath = APP . "Controller/{$dirPath}";
            if(is_dir($paramPath)){
                // We have a module!
                $this->request->addModule($ucfparam);
            }else if(is_file($paramPath . 'Controller.php')){
                // We have a controller!
                $this->request->setController($ucfparam);
            }else{
                $this->request->makeInvalid();
            }
        }else if(!$this->request->hasAction()){
            $this->request->setAction($param);
        }else{
            $this->request->addParameter($param);
        }
    }

    /**
    * Get and split the URL
    */
    private function splitUrl(){
        if (isset($_GET['url'])) {
            $this->hasParams = true;
            $url = trim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $explodedUrl = explode('/', $url);
            $this->urlParams = $explodedUrl != null ? $explodedUrl : array();
        }else{
            $this->urlParams = array();
        }
    }

    /**
    * Init Idiorm with user conigurations
    */
    private function initIdiorm(){
        if(DB_ENABLED){
            \ORM::configure(sprintf("%s:host=%s;dbname=%s", DB_TYPE, DB_HOST, DB_NAME));
            \ORM::configure('username', DB_USER);
            \ORM::configure('password', DB_PASS);
        }
    }

    /**
    * Changes the application environment
    */
    private function setEnvironment(){
        if (ENVIRONMENT == 'development' || ENVIRONMENT == 'dev') {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        }
    }
}
