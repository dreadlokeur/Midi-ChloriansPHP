<?php

namespace models;

use framework\mvc\model\Entity;

class User extends Entity {

    /**
     * @column(
     *      type="integer",
     *      unique="true",
     *      notNull="true",
     *      primary="true",
     *      required="true",
     *      autoIncrement="true"
     * )
     */
    protected $_id;

    /**
     * @column(
     *      type="string",
     *      length="255",
     *      notNull="true",
     *      required="true"
     * )
     */
    protected $_name;

    /**
     * @relation(
     *      type="oneToMany",
     *      entityTarget="models\Article",
     *      columnTarget="userId",
     *      columnParent="id"
     * )
     */
    protected $_articles;
    
    /**
     * @relation(
     *      type="oneToMany",
     *      entityTarget="models\Comment",
     *      columnTarget="userId",
     *      columnParent="id"
     * )
     */
    protected $_comments;

    public function __construct() {
        
    }

}

?>