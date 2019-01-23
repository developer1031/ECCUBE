<?php
namespace Plugin\Efo;

use Eccube\Application;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\Efo\Entity\Config;
use Plugin\Efo\Entity\CustomerProperty;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    /**
     * @param array $config
     * @param \Eccube\Application $app
     */
    public function install(array $config, Application $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
    }

    /**
     * @param array $config
     * @param \Eccube\Application $app
     */
    public function uninstall(array $config, Application $app)
    {
        $this->uninstallData($app);
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * @param array $config
     * @param \Eccube\Application $app
     */
    public function enable(array $config, Application $app)
    {
        if (!$this->isInstalled($app)) {
            $this->initializeData($app);
        }

        $this->deployAssets($app);
    }

    /**
     * @param array $config
     * @param \Eccube\Application $app
     */
    public function disable(array $config, Application $app)
    {
        $this->undeployAssets($app);
    }

    /**
     * @param array $config
     * @param \Eccube\Application $app
     */
    public function update(array $config, Application $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
        $this->deployAssets($app);
    }

    /**
     * @param \Eccube\Application $app
     */
    protected function initializeData(Application $app)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];
        $em->beginTransaction();

        $Config = new Config();
        $Config->setId(1);
        $Config->setShoppingLoginDestination(0);
        $em->persist($Config);

        foreach ($this->getDefaultCustomerProperties() as $property) {
            $Property = new CustomerProperty();
            $Property->setProperty($property['property']);
            $Property->setLabel($property['label']);
            $Property->setEnabled($property['enabled']);
            $Property->setRank($property['rank']);
            $em->persist($Property);
        }

        $em->flush();
        $em->commit();
    }

    /**
     * @param \Eccube\Application $app
     */
    protected function uninstallData(Application $app)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];
        $em->beginTransaction();

        $em->getFilters()->disable('soft_delete');

        $em->createQueryBuilder()
            ->delete('Plugin\\Efo\\Entity\\Config', 'c')
            ->getQuery()
            ->execute();

        $em->createQueryBuilder()
            ->delete('Plugin\\Efo\\Entity\\EntryForm', 'entry_form')
            ->getQuery()
            ->execute();

        $em->createQueryBuilder()
            ->delete('Eccube\\Entity\\PageLayout', 'page_layout')
            ->andWhere('page_layout.url LIKE :url')
            ->setParameter(':url', 'plugin_efo_entry_form_%')
            ->getQuery()
            ->execute();

        $em->flush();
        $em->commit();
    }

    protected function isInstalled(Application $app)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];

        /** @var \Plugin\Efo\Repository\ConfigRepository $configRepository */
        $configRepository = $em->getRepository('Plugin\\Efo\\Entity\\Config');

        return $configRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    /**
     * @return array
     */
    protected function getDefaultCustomerProperties()
    {
        return array(
            array(
                'property' => 'fax',
                'label'    => 'FAX',
                'enabled'  => false,
                'rank'     => 1,
            ),
            array(
                'property' => 'company_name',
                'label'    => '会社名',
                'enabled'  => true,
                'rank'     => 2,
            ),
            array(
                'property' => 'sex',
                'label'    => '性別',
                'enabled'  => true,
                'rank'     => 3,
            ),
            array(
                'property' => 'job',
                'label'    => '職業',
                'enabled'  => true,
                'rank'     => 4,
            ),
            array(
                'property' => 'birth',
                'label'    => '生年月日',
                'enabled'  => true,
                'rank'     => 5,
            ),
        );
    }

    protected function deployAssets(Application $app)
    {
        $file = new Filesystem();
        $file->mirror(__DIR__ . '/Resource/assets', $app['config']['root_dir'] . '/html/plugin/efo');
    }

    protected function undeployAssets(Application $app)
    {
        $file = new Filesystem();
        $file->remove($app['config']['root_dir'] . '/html/plugin/efo');
    }
}
