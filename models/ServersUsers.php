<?php

class ServersUsers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $server;

    /**
     *
     * @var string
     */
    public $user;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setConnectionService('db');

        $this->hasMany(
            "server",
            "Servers",
            "id",
            ["alias" => "Server"]
        );

        $this->hasMany(
            "user",
            "Users",
            "id",
            ["alias" => "User"]
        );
    }


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'servers_users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
