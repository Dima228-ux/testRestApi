<?php
require_once "db\DbConnector.php";
require_once "help\Request.php";

class User
{
    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return Request::i();
    }

    public function getUser($id, $auth)
    {
        $result = $this->checkAuthString(null, $auth);

        if (!$result) {
            return json_encode(['message' => 'Missing token']);
        }

        if ($id < 1) {
            return json_encode(['message' => 'Missing id']);
        }

        $db = DbConnector::getConnection();
        $queryResult = $db->query("SELECT * FROM `users`WHERE `id`='$id' ");
        $users = [];

        if (mysqli_num_rows($queryResult) == 0) {
            return json_encode(["message" => 'Error id user']);
        }

        while ($row = $queryResult->fetch_assoc()) {
            $users[] = [
                'id' => $row['id'],
                'login' => $row['login'],
                'email' => $row['email'],
                'name' => $row['name']
            ];

        }
        return json_encode($users);

    }

    public function deleteUser($array_params)
    {
        foreach ($array_params as $param) {
            $result = $this->checkAuthString($param);
            if (!$result) {
                $id = $this->getRequest()->getParamUrl($param, 2, 'id');
                if ($id < 1 || !$id) {
                    return json_encode(['message' => 'Missing param token or id']);
                }
            }
        }

        $db = DbConnector::getConnection();

        $result = $db->query(" DELETE FROM `users` WHERE `id`={$id}");


        if ($result) {
            return json_encode(['message' => 'User delete success']);
        }
        return json_encode(['message' => 'Error delete user']);
    }

    public function editUser($email, $login, $password, $name, $id)
    {

        $db = DbConnector::getConnection();
        $queryResult = $db->query("SELECT * FROM `users` 
WHERE`id`='$id' ");

        if (mysqli_num_rows($queryResult) == 0) {
            return json_encode(["message" => 'Error id']);
        } else {
            $result = $db->query("UPDATE `users` SET `name`='$name',`email`='$email' ,`password`='$password',`login`='$login'
WHERE `id`='$id' ");

        }
        if ($result) {
            return json_encode(['message' => 'User update success']);
        }
        return json_encode(['message' => 'Error update  user']);
    }


    public function authUser($data)
    {
        $password = md5($data['password']);
        $login = $data['login'];

        $db = DbConnector::getConnection();

        $queryResult = $db->query("SELECT * FROM `users` WHERE login='$login' AND password='$password'");

        if (mysqli_num_rows($queryResult) == 0) {
            return json_encode(["message" => 'Error auth']);
        } else {
            $random = $this->genString();
            $db->query("UPDATE `users` SET `auth_string`='$random' WHERE `login`='$login'");

            return json_encode(['auth_string' => $random]);
        }

    }

    public function checkUser($data, $edit = false, $token = null)
    {
        $email = $data['email'];
        $password = $data['password'];
        $login = $data['login'];
        $name = $data['name'];
        $id = $data['id'];

        $result = $this->filterData($email, $login, $password, $edit, $token);

        if ($result !== true) {
            if ($edit && !$result) {
                return json_encode(['message' => 'Error token']);
            }
            return $result;
        }

        $db = DbConnector::getConnection();

        $and = '';

        if ($edit) {
            if ($id < 1) {
                return json_encode(['message' => 'Id missing']);
            }

            $and = " AND id!='$id'";
        }

        $result_query = $db->query("SELECT * FROM `users` WHERE (`login`='$login' OR `email`='$email') {$and} ");

        if (mysqli_num_rows($result_query) > 0) {
            return json_encode(['message' => 'This login or email already exist']);
        }

        if ($edit) {
            return $this->editUser($email, $login, md5($password), $name, $id);
        }

        return $this->addUser($email, $login, md5($password), $name);

    }

    private function addUser($email, $login, $password, $name)
    {
        $db = DbConnector::getConnection();

        $result = $db->query(" INSERT INTO `users`(`name`, `login`, `password`, `email`) VALUES ('$name','$login','$password','$email') ");

        if ($result) {
            return json_encode(['message' => 'New user add success']);
        }
        return json_encode(['message' => 'Error add new user']);
    }

    private function filterData($email, $login, $password, $edit = false, $token = null)
    {
        if (empty(trim($email)) || empty(trim($password)) || empty(trim($login))) {
            return json_encode(['message' => 'Empty data']);
        }

        $pattern = '/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/';
        preg_match($pattern, $email, $check);

        if (count($check) < 2) {

            return json_encode(['message' => 'Error email']);
        }

        if (strlen(trim($login)) < 5 || strlen(trim($password)) < 5) {
            return json_encode(['message' => 'Error login or password']);
        }

        if ($edit) {
            return $this->checkAuthString($token);
        }

        return true;
    }

    public function genString()
    {
        $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $strlength = strlen($characters);
        $random = '';
        for ($i = 0; $i < 64; $i++) {
            $random .= $characters[rand(0, $strlength - 1)];
        }

        return $random;
    }

    /**
     * @param null $token
     * @param null $auth
     * @return bool
     */
    private function checkAuthString($token = null, $auth = null)
    {
        if ($auth === null) {
            $pos = mb_strpos($token, '=');
            if ($pos != 5) {
                return false;
            }
            $name_param = substr($token, 0, $pos);

            if ($name_param !== "token") {
                return false;
            }
            $auth = substr(substr($token, $pos, strlen($token)), 1);
        }

        if (strlen($auth) != 64) {
            return false;
        }

        $db = DbConnector::getConnection();
        $queryResult = $db->query("SELECT * FROM `users` 
WHERE`auth_string`='$auth' ");

        if (mysqli_num_rows($queryResult) == 0) {
            return false;
        }


        return true;
    }


}