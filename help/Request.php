<?php

/**
 * Class Request
 */
class Request
{
    const METHOD_GET = 1;

    /**
     * @var static
     */
    protected static $instance;

    /**
     * @return self
     */
    public static function i()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $method
     * @return bool
     */
    public function checkMethod($method)
    {
        if ($_SERVER['REQUEST_METHOD'] == $method) {
            return true;
        }
        return false;
    }

    /**
     * @param      $key
     * @param null $default_value
     *
     * @return int|null
     */
    public function getInt($key, $default_value = null)
    {
        return $this->getParamInt($key, self::METHOD_GET, $default_value);
    }

    /**
     * @param $key
     * @param null $default_value
     * @param int $min_value
     * @return string|null
     */
    public function getString($key, $default_value = null, $min_value = 0)
    {
        return $this->getParamString($key, self::METHOD_GET, $default_value, $min_value);
    }

    /**
     * @param string $key
     * @param int $source
     * @param mixed|null $default_value
     * @return int|null
     */
    protected function getParamInt($key, $source = self::METHOD_GET, $default_value = null)
    {
        $value = $this->getParam($key, $default_value);

        if (!is_numeric($value)) {
            return $default_value;
        }

        return $value;
    }

    /**
     * @param $key
     * @param int $source
     * @param null $default_value
     * @param $min_value
     * @return mixed|string|null
     */
    protected function getParamString($key, $source = self::METHOD_GET, $default_value = null, $min_value)
    {
        $value = $this->getParam($key, $default_value);

        if (!empty(trim($value)) && strlen(trim($value)) < $min_value) {
            return $default_value;
        }

        return $value;
    }

    /**
     * @param string $key
     * @param mixed|null $default_value
     *
     * @return mixed|null
     */
    protected function getParam($key, $default_value = null)
    {
        $value = isset($_GET[$key]) ? $_GET[$key] : $default_value;
        if (!is_string($value) || $value === '') {
            return $default_value;
        }
        return $value;
    }

    /**
     * @info получает массив из body x-www-form-url-encoded & row
     * @return false|mixed
     */
    public function getBodyParams($post = false)
    {

        $body = json_decode(file_get_contents('php://input'), true);

        if ($body == null && !$post) {
            parse_str(file_get_contents("php://input"), $body);
            if ($body == null) {
                return false;
            }
            return $body;
        } elseif ($post && $body == null) {
            $body = $_POST;
            if ($body == null) {
                parse_str(file_get_contents("php://input"), $body);
                if ($body == null) {
                    return false;
                }
                return $body;
            }
            return $body;
        }
        return $body;
    }

    /**
     * @info достает парметр из строки полученной на роутере из урл
     * @param $param // параметр пример id=2334
     * @param int $must_pos // позиция =,для проверки правильности парметра
     * @param $key //название параметра
     * @return false|string
     */
    public function getParamUrl($param, int $must_pos, $key)
    {
        $pos = mb_strpos($param, '=');
        if ($pos != $must_pos) {
            return false;
        }

        $name_param = substr($param, 0, $pos);
        if ($name_param !== $key) {
            return false;
        }

        $param = substr(substr($param, $pos, strlen($param)), 1);

        return $param;
    }


}