<?php

namespace Plugin\Efo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\PageLayout;
use Plugin\Efo\Entity\EntryForm;
use Plugin\Efo\Repository\EntryFormRepository;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouteCollection;

class EntryFormService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Eccube\Application
     */
    protected $app;

    /**
     * @var \Plugin\Efo\Repository\EntryFormRepository
     */
    protected $entryFormRepository;

    /**
     * @var \Plugin\Efo\Service\ConfigService
     */
    protected $configService;

    /**
     * EntryFormService constructor.
     *
     * @param \Silex\Application $app
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Plugin\Efo\Repository\EntryFormRepository $entryFormRepository
     * @param \Plugin\Efo\Service\ConfigService $configService
     */
    public function __construct(Application $app, EntityManagerInterface $entityManager, EntryFormRepository $entryFormRepository, ConfigService $configService)
    {
        $this->app = $app;
        $this->entityManager = $entityManager;
        $this->entryFormRepository = $entryFormRepository;
        $this->configService = $configService;
    }

    /**
     * @param \Plugin\Efo\Entity\EntryForm $EntryForm
     */
    public function create(EntryForm $EntryForm)
    {
        $this->entityManager->persist($EntryForm->getPageLayout());
        $this->entityManager->flush();

        $this->entryFormRepository->save($EntryForm);

        $this->updatePageLayout($EntryForm, true);
    }

    /**
     * @param \Plugin\Efo\Entity\EntryForm $EntryForm
     */
    protected function updatePageLayout(EntryForm $EntryForm, $resetTemplate = false)
    {
        $PageLayout = $EntryForm->getPageLayout();

        $PageLayout->setName('注文フォーム/' . $EntryForm->getName());
        $PageLayout->setUrl('plugin_efo_entry_form_' . $EntryForm->getId() . '_index');
        $PageLayout->setFileName('Plugin/Efo/EntryForm/' . $EntryForm->getId());

        $this->entityManager->persist($EntryForm);
        $this->entityManager->flush();

        /** @var \Eccube\Repository\PageLayoutRepository $pageLayoutRepository */
        $pageLayoutRepository = $this->app['eccube.repository.page_layout'];
        $templatePath = $pageLayoutRepository->getWriteTemplatePath();
        $dest = $templatePath . '/' . $PageLayout->getFileName() . '.twig';

        if ($resetTemplate || !file_exists($dest)) {
            $original = $this->app['config']['plugin_realdir'] . '/Efo/Resource/efo/entry_form.twig';
            $fs = new Filesystem();
            $fs->copy($original, $dest);
        }
    }

    /**
     * @param \Plugin\Efo\Entity\EntryForm $EntryForm
     */
    public function update(EntryForm $EntryForm)
    {
        $this->entryFormRepository->save($EntryForm);

        $this->updatePageLayout($EntryForm);
    }

    /**
     * @param \Plugin\Efo\Entity\EntryForm $EntryForm
     */
    public function delete(EntryForm $EntryForm)
    {
        $this->entityManager->remove($EntryForm->getPageLayout());

        $this->entryFormRepository->delete($EntryForm);
    }

    /**
     * @return \Plugin\Efo\Entity\EntryForm
     */
    public function newInstance()
    {
        $EntryForm = new EntryForm();

        $EntryForm->setPath('/');
        $EntryForm->setCustomerRegistrationEnabled(true);
        $EntryForm->setDelFlg(0);

        $PageLayout = $this->createDefaultPageLayout();
        $EntryForm->setPageLayout($PageLayout);

        return $EntryForm;
    }

    /**
     * @return \Eccube\Entity\PageLayout|mixed
     */
    protected function createDefaultPageLayout()
    {
        /** @var \Eccube\Repository\PageLayoutRepository $pageLayoutRepository */
        $pageLayoutRepository = $this->app['eccube.repository.page_layout'];

        /** @var \Eccube\Repository\Master\DeviceTypeRepository $deviceTypeRepository */
        $deviceTypeRepository = $this->app['eccube.repository.master.device_type'];

        /** @var \Eccube\Entity\Master\DeviceType $DeviceType */
        $DeviceType = $deviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC);

        $PageLayout = $pageLayoutRepository->findOrCreate(null, $DeviceType);

        $PageLayout->setName('注文フォーム');
        $PageLayout->setUrl('');
        $PageLayout->setFileName('Efo/Resource/template/default/efo/_');
        $PageLayout->setEditFlg(PageLayout::EDIT_FLG_DEFAULT);
        $PageLayout->setMetaRobots('');

        return $PageLayout;
    }

    /**
     * @param string $path
     * @return \Plugin\Efo\Entity\EntryForm
     */
    public function findByPath($path)
    {
        return $this->entryFormRepository->findOneByPath($path);
    }

    /**
     * @param \Silex\ControllerCollection $controllers
     * @param \Symfony\Component\Routing\RouteCollection $routes
     */
    public function registerRoutes(ControllerCollection $controllers, RouteCollection $routes)
    {
        $disableDefaultRoot = false;

        foreach ($this->getRoutes() as $route) {
            $controllers
                ->match($route['path'], $route['action'])
                // ->method($route['method'])
                ->bind($route['name']);

            $disableDefaultRoot = $disableDefaultRoot || $route['path'] === '/';
        }

        if ($disableDefaultRoot) {
            /** @var \Symfony\Component\Routing\Route $route */
            foreach ($routes as $route) {
                if ($route->getPath() === '/') {
                    $route->setMethods('UNKNOWN');
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function getRoutes()
    {
        $routes = array();

        $EntryForms = $this->entryFormRepository->findByActive();

        foreach ($EntryForms as $EntryForm) {
            $routes[] = array(
                'path'   => $EntryForm->getPath(),
                'action' => '\\Plugin\\Efo\\Controller\\Front\\Shopping\\EntryFormController::index',
                //                'method' => 'get',
                'name'   => 'plugin_efo_entry_form_' . $EntryForm->getId() . '_index',
            );

//            $routes[] = array(
//                'path'   => $EntryForm->getPath(),
//                'action' => '\\Plugin\\Efo\\Controller\\Front\\Shopping\\EntryFormController::store',
//                'method' => 'post',
//                'name'   => 'plugin_efo_entry_form_' . $EntryForm->getId() . '_store',
//            );
        }

        return $routes;
    }
}
