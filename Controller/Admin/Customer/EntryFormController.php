<?php

namespace Plugin\Efo\Controller\Admin\Customer;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class EntryFormController extends AbstractController
{
    public function index(Application $app, Request $request)
    {
        /** @var \Plugin\Efo\Service\CustomerPropertyService $customerPropertyService */
        $customerPropertyService = $app['eccube.plugin.efo.service.customer_property'];

        $Properties = $customerPropertyService->all();

        /** @var \Plugin\Efo\Service\ConfigService $configService */
        $configService = $app['eccube.plugin.efo.service.config'];
        $Config = $configService->get();

        $data = array(
            'properties'                 => $Properties,
            'shopping_login_destination' => $Config->getShoppingLoginDestination(),
        );

        /** @var \Symfony\Component\Form\FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $formBuilder = $formFactory
            ->createNamedBuilder('search', 'plugin_efo_admin_customer_entry_form', $data)
            ->setMethod('post');

        /** @var \Symfony\Component\Form\Form $form */
        $form = $formBuilder->getForm();

        return $app->render('Efo/Resource/template/admin/Customer/EntryForm/index.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function store(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];

        $entityManager->beginTransaction();

        /** @var \Plugin\Efo\Service\CustomerPropertyService $customerPropertyService */
        $customerPropertyService = $app['eccube.plugin.efo.service.customer_property'];

        $Properties = $customerPropertyService->all();

        $data = array(
            'properties' => $Properties,
        );

        /** @var FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $formBuilder = $formFactory
            ->createNamedBuilder('search', 'plugin_efo_admin_customer_entry_form', $data)
            ->setMethod('post');

        /** @var \Symfony\Component\Form\Form $form */
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $entityManager->rollback();

            return $app->render('Efo/Resource/template/admin/Customer/EntryForm/index.twig', array(
                'form' => $form->createView(),
            ));
        }

        $data = $form->getData();

        foreach ($data['properties'] as $Property) {
            $customerPropertyService->update($Property);
        }

        /** @var \Plugin\Efo\Service\ConfigService $configService */
        $configService = $app['eccube.plugin.efo.service.config'];
        $Config = $configService->get();

        $Config->setShoppingLoginDestination($data['shopping_login_destination']);

        $configService->update($Config);

        $entityManager->commit();

        $app->addSuccess('注文フォームを保存しました', 'admin');

        return $app->redirect($app->url('plugin_Efo_admin_customer_entry_form_index'));
    }

}
