<?php
require_once 'db\DbManager.php';
require_once 'controllers\BaseController.php';

/**
 * Class ApiController
 */
class ApiController extends BaseController
{
    private $dbManager;

    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->dbManager = new DbManager();

    }

    public function indexAction()
    {
        echo json_encode(['message' => 'Welcome to Api']);
        exit();
    }

    public function createUserAction()
    {
        if (!$this->getRequest()->checkMethod("POST")) {
            echo json_encode(['message' => 'Error request']);
            exit();
        }
        $post = $this->getRequest()->getBodyParams(true);

        if (!$post) {
            echo json_encode(['message' => 'Error data']);
            exit();
        }
        echo $this->dbManager->User->checkUser($post);
        exit();
    }


    public function authUserAction()
    {
        if (!$this->getRequest()->checkMethod("POST")) {
            echo json_encode(['message' => 'Error request']);
            exit();
        }

        $post = $this->getRequest()->getBodyParams(true);

        if (!$post) {
            echo json_encode(['message' => 'Error data']);
            exit();
        }

        echo $this->dbManager->User->authUser($post);
        exit();
    }

    /**
     * @param null $token
     */
    public function editUserAction($token = null)
    {
        if (!$this->getRequest()->checkMethod("PUT")) {
            echo json_encode(['message' => 'Error request']);
            exit();
        }

        $data = $this->getRequest()->getBodyParams();

        if (!$data) {
            echo json_encode(['message' => 'Error data']);
            exit();
        }

        echo $this->dbManager->User->checkUser($data, true, $token);
        exit();
    }

    /**
     * @param null $params
     */
    public function deleteUserAction($params = null)
    {
        if (!$this->getRequest()->checkMethod("DELETE")) {
            echo json_encode(['message' => 'Error request']);
            exit();
        }
        $array_params = explode('&', $params);

        if (count($array_params) != 2) {
            echo json_encode(['message' => 'Empty params']);
            exit();
        }

        echo $this->dbManager->User->deleteUser($array_params);
        exit();
    }

    public function getUserAction()
    {
        if (!$this->getRequest()->checkMethod("GET")) {
            echo json_encode(['message' => 'Error request']);
            exit();
        }

        $id = $this->getRequest()->getInt('id');
        $token = $this->getRequest()->getString('token', null, 64);
        echo $this->dbManager->User->getUser($id, $token);
        exit();

    }

}