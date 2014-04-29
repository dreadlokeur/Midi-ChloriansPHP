<?php

namespace models\entities;

use framework\mvc\model\Entity;

class Comment extends Entity {

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
     *      type="string"
     * )
     */
    protected $_content;

    /**
     * @column(
     *      type="integer",
     *      notNull="true",
     *      foreign="true"
     * )
     */
    protected $_userId;

    /**
     * @relation(
     *      type="manyToOne",
     *      entityTarget="models\entities\User",
     *      columnTarget="id",
     *      columnParent="userId"
     * )
     */
    protected $_user;

    public function __construct() {
        
    }

}

?>