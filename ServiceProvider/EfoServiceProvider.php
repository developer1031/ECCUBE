<?php

namespace Plugin\Efo\ServiceProvider;

use Eccube\Application as EccubeApplication;
use Silex\Application;
use Silex\ServiceProviderInterface;

class EfoServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Silex\Application $app
     */
    public function register(Application $app)
    {
        $this->registerConfig($app);
        $this->registerServices($app);
        $this->registerRepository($app);
        $this->registerRouting($app);
        $this->registerForms($app);
        $this->registerLogger($app);
    }

    /**
     * @param \Silex\Application $app
     */
    protected function registerConfig(Application $app)
    {
        $app['config'] = $app->extend('config', function (array $config) {
            $config['efo_assets_realdir'] = (isset($config['public_path_realdir']) ? $config['public_path_realdir'] : $config['root_dir']) . '/plugin/efo';
            $config['efo_assets_urlpath'] = (isset($config['public_path']) ? $config['public_path'] : $config['root_urlpath']) . '/plugin/efo';

            return $config;
        });
    }

    /**
     * @param \Silex\Application $app
     */
    protected function registerServices(Application $app)
    {
        $app['eccube.plugin.efo.service.config'] = $app->share(function () use ($app) {
            /** @var \Doctrine\ORM\EntityManagerInterface $em */
            $em = $app['orm.em'];

            /** @var \Plugin\Efo\Repository\ConfigRepository $configRepository */
            $configRepository = $app['eccube.plugin.efo.repository.config'];

            $entryFormService = new \Plugin\Efo\Service\ConfigService($em, $configRepository);

            return $entryFormService;
        });

        $app['eccube.plugin.efo.service.entry_form'] = $app->share(function () use ($app) {
            /** @var \Doctrine\ORM\EntityManagerInterface $em */
            $em = $app['orm.em'];

            /** @var \Plugin\Efo\Repository\EntryFormRepository $entryFormRepository */
            $entryFormRepository = $app['eccube.plugin.efo.repository.entry_form'];

            /** @var \Plugin\Efo\Service\ConfigService $configService */
            $configService = $app['eccube.plugin.efo.service.config'];

            $entryFormService = new \Plugin\Efo\Service\EntryFormService($app, $em, $entryFormRepository, $configService);

            return $entryFormService;
        });

        $app['eccube.plugin.efo.service.customer_property'] = $app->share(function () use ($app) {
            /** @var \Doctrine\ORM\EntityManagerInterface $em */
            $em = $app['orm.em'];

            /** @var \Plugin\Efo\Repository\CustomerPropertyRepository $customerPropertyRepository */
            $customerPropertyRepository = $app['eccube.plugin.efo.repository.customer_property'];

            $customerPropertyService = new \Plugin\Efo\Service\CustomerPropertyService($em, $customerPropertyRepository);

            return $customerPropertyService;
        });
    }

    protected function registerRepository(Application $app)
    {
        $app['eccube.plugin.efo.repository.config'] = $app->share(function (Application $app) {
            /** @var \Doctrine\ORM\EntityManagerInterface $em */
            $em = $app['orm.em'];

            return $em->getRepository('\\Plugin\\Efo\\Entity\\Config');
        });

        $app['eccube.plugin.efo.repository.entry_form'] = $app->share(function (Application $app) {
            /** @var \Doctrine\ORM\EntityManagerInterface $em */
            $em = $app['orm.em'];

            return $em->getRepository('\\Plugin\\Efo\\Entity\\EntryForm');
        });

        $app['eccube.plugin.efo.repository.customer_property'] = $app->share(function (Application $app) {
            /** @var \Doctrine\ORM\EntityManagerInterface $em */
            $em = $app['orm.em'];

            return $em->getRepository('\\Plugin\\Efo\\Entity\\CustomerProperty');
        });
    }

    /**
     * @param \Silex\Application $app
     */
    protected function registerRouting(Application $app)
    {
        $app['config'] = $app->share($app->extend('config', function (array $config) {
            $config['nav'] = array_map(function (array $item) {
                switch ($item['id']) {
                    case 'product':
                        $item['child'][] = array(
                            'id'   => 'plugin_efo_order_form',
                            'name' => '注文フォーム管理',
                            'url'  => 'plugin_Efo_admin_entry_form_index',
                        );

                        $item['child'][] = array(
                            'id'   => 'plugin_efo_order_form',
                            'name' => '注文フォーム登録',
                            'url'  => 'plugin_Efo_admin_entry_form_create',
                        );
                        break;

                    case 'customer':
                        $item['child'][] = array(
                            'id'   => 'plugin_efo_customer_form',
                            'name' => '会員登録フォーム管理',
                            'url'  => 'plugin_Efo_admin_customer_entry_form_index',
                        );
                        break;
                }

                return $item;
            }, $config['nav']);

            return $config;
        }));

        $app->get($app['config']['admin_route'] . '/plugin/efo/config', '\\Plugin\\Efo\\Controller\\Admin\\ConfigController::show')->bind('plugin_Efo_config');
        $app->put($app['config']['admin_route'] . '/plugin/efo/config', '\\Plugin\\Efo\\Controller\\Admin\\ConfigController::update');

        $app->get($app['config']['admin_route'] . '/plugin/efo/entry_form', '\\Plugin\\Efo\\Controller\\Admin\\EntryForm\\EntryFormController::index')->bind('plugin_Efo_admin_entry_form_index');
        $app->get($app['config']['admin_route'] . '/plugin/efo/entry_form/create', '\\Plugin\\Efo\\Controller\\Admin\\EntryForm\\EntryFormController::create')->bind('plugin_Efo_admin_entry_form_create');
        $app->post($app['config']['admin_route'] . '/plugin/efo/entry_form', '\\Plugin\\Efo\\Controller\\Admin\\EntryForm\\EntryFormController::store')->bind('plugin_Efo_admin_entry_form_store');
        $app->get($app['config']['admin_route'] . '/plugin/efo/entry_form/{id}/edit', '\\Plugin\\Efo\\Controller\\Admin\\EntryForm\\EntryFormController::edit')->bind('plugin_Efo_admin_entry_form_edit')->assert('id', '\d+');
        $app->put($app['config']['admin_route'] . '/plugin/efo/entry_form/{id}', '\\Plugin\\Efo\\Controller\\Admin\\EntryForm\\EntryFormController::update')->bind('plugin_Efo_admin_entry_form_update')->assert('id', '\d+');
        $app->delete($app['config']['admin_route'] . '/plugin/efo/entry_form/{id}', '\\Plugin\\Efo\\Controller\\Admin\\EntryForm\\EntryFormController::delete')->bind('plugin_Efo_admin_entry_form_delete')->assert('id', '\d+');

        $app->get($app['config']['admin_route'] . '/plugin/efo/customer/entry_form', '\\Plugin\\Efo\\Controller\\Admin\\Customer\\EntryFormController::index')->bind('plugin_Efo_admin_customer_entry_form_index');
        $app->post($app['config']['admin_route'] . '/plugin/efo/customer/entry_form', '\\Plugin\\Efo\\Controller\\Admin\\Customer\\EntryFormController::store')->bind('plugin_Efo_admin_customer_entry_form_store');
    }

    /**
     * @param \Silex\Application $app
     */
    protected function registerForms(Application $app)
    {
        $app['form.types'] = $app->share($app->extend('form.types', function (array $types) use ($app) {
            $types[] = new \Plugin\Efo\Form\Type\Admin\ConfigType($app);
            $types[] = new \Plugin\Efo\Form\Type\Admin\EntryFormType();
            $types[] = new \Plugin\Efo\Form\Type\Admin\EntryFormSearchType();
            $types[] = new \Plugin\Efo\Form\Type\AddCartType($app['config'], $app['security'], $app['eccube.repository.customer_favorite_product']);
            $types[] = new \Plugin\Efo\Form\Type\OrderFormType($app);
            $types[] = new \Plugin\Efo\Form\Type\Admin\CustomerEntryFormType();
            $types[] = new \Plugin\Efo\Form\Type\Admin\CustomerPropertyType();

            return $types;
        }));

        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function (array $extensions) use ($app) {
            $extensions[] = new \Plugin\Efo\Form\Extension\EntryTypeExtension($app);

            return $extensions;
        }));
    }

    /**
     * @param \Silex\Application $app
     */
    protected function registerLogger(Application $app)
    {
    }

    /**
     * @param \Silex\Application $app
     */
    public function boot(Application $app)
    {
        $this->overrideRoutes($app);
    }

    /**
     * @param \Silex\Application $app
     */
    protected function overrideRoutes(Application $app)
    {
        /** @var \Plugin\Efo\Service\EntryFormService $entryFormService */
        $entryFormService = $app['eccube.plugin.efo.service.entry_form'];

        /** @var \Silex\ControllerCollection $controllers */
        $controllers = $app['controllers'];

        /** @var \Symfony\Component\Routing\RouteCollection $routes */
        $routes = $app['routes'];

        $entryFormService->registerRoutes($controllers, $routes);

        // 会員登録手続きを省力化
        $app->match('/entry', '\\Plugin\\Efo\\Controller\\Front\\Entry\\EntryController::index')->bind('entry');
        $app->match('/entry/activate/{secret_key}', '\\Plugin\\Efo\\Controller\\Front\\Entry\\EntryController::activate')->bind('entry_activate');
    }
}
