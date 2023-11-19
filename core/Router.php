<?php


class Router
{
    private $url;
    private $method;


    /**
     * Router constructor.
     * @param $url
     */
    public function __construct($url, $method)
    {
        $this->url = $url;
        $this->method = $method;
    }

    /**
     * @return void
     */
    public function route()
    {

        $urlParts = explode("/", $this->url);

        if ($urlParts[1] == "") {
            $controllerName = "ApiController";
            $actionName = "indexAction";
        } else {
            $controllerName = $urlParts[1] . "Controller";
            $action = explode("?", $urlParts[2])[0];
            $name = explode('-', $action);
            $actionName = $name[0] . ucfirst($name[1]) . "Action";
        }

        $controllerPath = "./controllers/" . ucfirst($controllerName) . ".php";

        if (file_exists($controllerPath) === false) {

            echo json_encode(['message' => 'Error 404']);
            die();
        }
        $params = explode("?", $urlParts[2])[1];

        require_once $controllerPath;

        $controller = new $controllerName;
        $action = $actionName;

        if (method_exists($controller, $action) == false) {
            echo json_encode(['message' => 'Error 404']);
            die();
        }

        if ($this->method == 'POST') {
            $controller->$action();
        } elseif ($this->method == 'PUT' || $this->method == 'DELETE') {

            if (empty(trim($params))) {
                echo json_encode(['message' => 'Error params']);
                exit();
            }
            $controller->$action($params);

            $controller->$action($params);
        } elseif ($this->method == 'GET') {
            $controller->$action();
        }

        if ($controllerName != "ApiController") {
            echo json_encode(['message' => 'Error request']);
            exit();
        }

        $controller->$action();
    }
}