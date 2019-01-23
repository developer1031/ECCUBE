<?php

namespace Plugin\Efo\Repository;

use Doctrine\ORM\EntityRepository;
use Plugin\Efo\Entity\Config;

class ConfigRepository extends EntityRepository
{
    /**
     * @param \Plugin\Efo\Entity\Config $Config
     */
    public function save(Config $Config)
    {
        $this->getEntityManager()->persist($Config);
        $this->getEntityManager()->flush($Config);
    }

    /**
     * @param \Plugin\Efo\Entity\Config $Config
     */
    public function delete(Config $Config)
    {
        $this->getEntityManager()->remove($Config);
        $this->getEntityManager()->flush($Config);
    }
}
