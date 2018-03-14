<?php

class Json extends Phalcon\Mvc\User\Component
{
    protected $jsonData = [];

    public function __construct()
    {
        $this->jsonData = $this->request->getJsonRawBody(true);
    }

    public function getPut(string $key = null)
    {
        if ($key) {
            if (array_key_exists($key, $this->jsonData))
                return $this->jsonData[$key];
            else
                return null;
        } else {
            return $this->jsonData;
        }
    }

    public function getPost(string $key = null)
    {
        return $this->getPut($key);
    }
}