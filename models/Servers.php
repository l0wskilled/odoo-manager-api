<?php

class Servers extends \Phalcon\Mvc\Model
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
    public $name;

    /**
     *
     * @var string
     */
    public $localeIp;

    /**
     *
     * @var string
     */
    public $remoteIp;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setConnectionService('db');
        $this->hasMany(
            "id",
            "ServersUsers",
            "server",
            ["alias" => "ServersUsers"]
        );
        $this->hasManyToMany(
            "id",
            "ServersUsers",
            "server",
            "user",
            "Users",
            "id",
            ["alias" => "Users"]
        );
    }


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'servers';
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
