<?php

namespace Plugin\Efo\Entity;

use Eccube\Entity\AbstractEntity;

class Config extends AbstractEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $shopping_login_destination;

    /**
     * @var \DateTime
     */
    protected $create_date;

    /**
     * @var \DateTime
     */
    protected $update_date;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Config
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getShoppingLoginDestination()
    {
        return $this->shopping_login_destination;
    }

    /**
     * @param int $shopping_login_destination
     * @return Config
     */
    public function setShoppingLoginDestination($shopping_login_destination)
    {
        $this->shopping_login_destination = $shopping_login_destination;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param \DateTime $create_date
     * @return Config
     */
    public function setCreateDate(\DateTime $create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @param \DateTime $update_date
     * @return Config
     */
    public function setUpdateDate(\DateTime $update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }
}
