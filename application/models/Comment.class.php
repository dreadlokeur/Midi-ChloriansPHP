<?php

namespace models;

use framework\mvc\model\Entity;

class Comment extends Entity {

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
     *      required="true"
     * )
     */
    protected $_content;

    /**
     * @column(
     *      type="integer",
     *      notNull="true"
     *      foreign="true"
     *      required="true"
     * )
     */
    protected $_userId;

    /**
     * @relation(
     *      type="manyToOne",
     *      entityTarget="models\User",
     *      columnTarget="id",
     *      columnParent="userId"
     * )
     */
    protected $_user;

    public function __construct() {
        
    }

}

?>