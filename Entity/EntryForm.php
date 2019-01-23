<?php

namespace Plugin\Efo\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Product;

class EntryForm extends AbstractEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $product_id;

    /**
     * @var \Eccube\Entity\Product
     */
    protected $Product;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $page_id;

    /**
     * @var \Eccube\Entity\PageLayout
     */
    protected $PageLayout;

    /**
     * @var \DateTime
     */
    protected $create_date;

    /**
     * @var \DateTime
     */
    protected $update_date;

    /**
     * @var bool
     */
    protected $customer_registration_enabled;

    /**
     * @var bool
     */
    protected $del_flg;

    /**
     * @param int $product_id
     * @return EntryForm
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;

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
     * @return EntryForm
     */
    public function setUpdateDate(\DateTime $update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return EntryForm
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \Eccube\Entity\Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * @param \Eccube\Entity\Product $Product
     * @return EntryForm
     */
    public function setProduct(Product $Product)
    {
        $this->Product = $Product;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return EntryForm
     */
    public function setPath($path)
    {
        $this->path = $path;

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
     * @return EntryForm
     */
    public function setCreateDate(\DateTime $create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * @param boolean $del_flg
     */
    public function setDelFlg($del_flg)
    {
        $this->del_flg = $del_flg;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * @param int $page_id
     */
    public function setPageId($page_id)
    {
        $this->page_id = $page_id;
    }

    /**
     * @return \Eccube\Entity\PageLayout
     */
    public function getPageLayout()
    {
        return $this->PageLayout;
    }

    /**
     * @param \Eccube\Entity\PageLayout $PageLayout
     */
    public function setPageLayout(PageLayout $PageLayout)
    {
        $this->PageLayout = $PageLayout;
    }

    /**
     * @return string
     */
    public function getIndexRoute()
    {
        return 'plugin_efo_entry_form_' . $this->getId() . '_index';
    }

    /**
     * @return boolean
     */
    public function isCustomerRegistrationEnabled()
    {
        return $this->customer_registration_enabled;
    }

    /**
     * @param boolean $customer_registration_enabled
     * @return EntryForm
     */
    public function setCustomerRegistrationEnabled($customer_registration_enabled)
    {
        $this->customer_registration_enabled = $customer_registration_enabled;

        return $this;
    }
}
