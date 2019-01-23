<?php

namespace Plugin\Efo\Repository;

use Doctrine\ORM\EntityRepository;
use Plugin\Efo\Entity\CustomerProperty;

class CustomerPropertyRepository extends EntityRepository
{
    /**
     * @param \Plugin\Efo\Entity\CustomerProperty $Property
     */
    public function save(CustomerProperty $Property)
    {
        $this->getEntityManager()->persist($Property);
        $this->getEntityManager()->flush($Property);
    }

    /**
     * @param \Plugin\Efo\Entity\CustomerProperty $Property
     */
    public function delete(CustomerProperty $Property)
    {
        $this->getEntityManager()->remove($Property);
        $this->getEntityManager()->flush($Property);
    }
}
