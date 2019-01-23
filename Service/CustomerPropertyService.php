<?php

namespace Plugin\Efo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Plugin\Efo\Entity\CustomerProperty;
use Plugin\Efo\Repository\CustomerPropertyRepository;
use Silex\Application;

class CustomerPropertyService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Plugin\Efo\Repository\CustomerPropertyRepository
     */
    protected $customerPropertyRepository;

    /**
     * CustomerPropertyService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Plugin\Efo\Repository\CustomerPropertyRepository $customerPropertyRepository
     */
    public function __construct(EntityManagerInterface $entityManager, CustomerPropertyRepository $customerPropertyRepository)
    {
        $this->entityManager = $entityManager;
        $this->customerPropertyRepository = $customerPropertyRepository;
    }

    /**
     * @param \Plugin\Efo\Entity\CustomerProperty $CustomerProperty
     */
    public function create(CustomerProperty $CustomerProperty)
    {
        $this->customerPropertyRepository->save($CustomerProperty);
    }

    /**
     * @param \Plugin\Efo\Entity\CustomerProperty $CustomerProperty
     */
    public function update(CustomerProperty $CustomerProperty)
    {
        $this->customerPropertyRepository->save($CustomerProperty);
    }

    /**
     * @param \Plugin\Efo\Entity\CustomerProperty $CustomerProperty
     */
    public function delete(CustomerProperty $CustomerProperty)
    {
        $this->customerPropertyRepository->delete($CustomerProperty);
    }

    /**
     * @return \Plugin\Efo\Entity\CustomerProperty
     */
    public function newInstance()
    {
        $CustomerProperty = new CustomerProperty();

        return $CustomerProperty;
    }

    /**
     * @param \Plugin\Efo\Entity\CustomerProperty $customerProperty
     */
    public function reset(CustomerProperty $customerProperty)
    {
    }

    /**
     * return \Plugin\Efo\Entity\CustomerProperty[]
     */
    public function all()
    {
        return $this->customerPropertyRepository->findAll();
    }

    /**
     * @return array
     */
    public function getEnabledPropertyNames()
    {
        $Properties = array_filter($this->all(), function (CustomerProperty $Property) {
            return $Property->isEnabled();
        });

        return array_map(function (CustomerProperty $Property) {
            return $Property->getProperty();
        }, $Properties);
    }
}
