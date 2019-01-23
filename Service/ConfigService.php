<?php

namespace Plugin\Efo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Plugin\Efo\Entity\Config;
use Plugin\Efo\Repository\ConfigRepository;
use Silex\Application;

class ConfigService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Plugin\Efo\Repository\ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Plugin\Efo\Repository\ConfigRepository $configRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ConfigRepository $configRepository)
    {
        $this->entityManager = $entityManager;
        $this->configRepository = $configRepository;
    }

    /**
     * @param \Plugin\Efo\Entity\Config $Config
     */
    public function create(Config $Config)
    {
        $this->configRepository->save($Config);
    }

    /**
     * @param \Plugin\Efo\Entity\Config $Config
     */
    public function update(Config $Config)
    {
        $this->configRepository->save($Config);
    }

    /**
     * @param \Plugin\Efo\Entity\Config $Config
     */
    public function delete(Config $Config)
    {
        $this->configRepository->delete($Config);
    }

    /**
     * @return \Plugin\Efo\Entity\Config
     */
    public function newInstance()
    {
        $Config = new Config();

        return $Config;
    }

    /**
     * @param \Plugin\Efo\Entity\Config $config
     */
    public function reset(Config $config)
    {
    }

    /**
     * @return \Plugin\Efo\Entity\Config
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get()
    {
        return $this->configRepository
            ->createQueryBuilder('config')
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @return array
     */
    public function getShoppingLoginDestination()
    {
        $Config = $this->get();

        switch ($Config->getShoppingLoginDestination()) {
            case 0:
                return array();

            case 1:
                return array('entry', array());

            case 2:
                return array('shopping_nonmember', array());
        }

        throw new \RuntimeException('Unknown shopping login destination.');
    }
}
