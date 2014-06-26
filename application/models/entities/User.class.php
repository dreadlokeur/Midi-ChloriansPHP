<?php

namespace models\entities;

use framework\mvc\model\Entity;

class User extends Entity {

    /**
     * @column(
     *      type="integer",
     *      unique="true",
     *      notNull="true",
     *      primary="true",
     *      autoIncrement="true"
     * )
     */
    protected $_id;

    /**
     * @column(
     *      type="string",
     *      length="255",
     *      notNull="true"
     * )
     */
    protected $_name;

    /**
     * @relation(
     *      type="oneToMany",
     *      entityTarget="models\entities\Article",
     *      columnTarget="userId",
     *      columnParent="id"
     * )
     */
    protected $_articles;

    /**
     * @relation(
     *      type="oneToMany",
     *      entityTarget="models\entities\Comment",
     *      columnTarget="userId",
     *      columnParent="id"
     * )
     */
    protected $_comments;

    public function __construct() {
        
    }

}

?>