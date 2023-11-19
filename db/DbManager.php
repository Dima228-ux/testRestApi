<?php
require_once "models\User.php";

class DbManager
{
    public $User;

    /**
     * DbManager constructor.
     * @param $User
     */
    public function __construct()
    {
        $this->User = new User();
    }
}