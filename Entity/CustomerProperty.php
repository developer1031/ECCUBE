<?php
namespace Plugin\Efo\Entity;

class CustomerProperty
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */

    protected $property;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var int
     */
    protected $rank;

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
     * @return CustomerProperty
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     * @return CustomerProperty
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return CustomerProperty
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return CustomerProperty
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     * @return CustomerProperty
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

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
     * @return CustomerProperty
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
     * @return CustomerProperty
     */
    public function setUpdateDate(\DateTime $update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }
}
