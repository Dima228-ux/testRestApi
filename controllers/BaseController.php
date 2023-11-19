<?php
require_once "help\Request.php";

class BaseController
{
    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return Request::i();
    }
}