<?php

class UsersAccess extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $user;

    /**
     *
     * @var string
     */
    public $ip;

    /**
     *
     * @var string
     */
    public $domain;

    /**
     *
     * @var string
     */
    public $country;

    /**
     *
     * @var string
     */
    public $browser;

    /**
     *
     * @var string
     */
    public $date;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setConnectionService('db');
        $this->belongsTo(
            "user",
            "Users",
            "id"
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'users_access';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersAccess[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersAccess
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return array(
            'id' => 'id',
            'user' => 'user',
            'ip' => 'ip',
            'domain' => 'domain',
            'country' => 'country',
            'browser' => 'browser',
            'date' => 'date',
        );
    }

}
