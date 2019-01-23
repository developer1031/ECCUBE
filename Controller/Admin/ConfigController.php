<?php
namespace Plugin\Efo\Controller\Admin;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class ConfigController
{
    public function show(Application $app, Request $request)
    {
        /** @var \Plugin\Efo\Repository\ConfigRepository $configRepository */
        $configRepository = $app['eccube.plugin.efo.repository.config'];

        /** @var \Plugin\Efo\Entity\Config $Config */
        $Config = $configRepository->find(1);

        /** @var \Symfony\Component\Form\FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $form = $formFactory->createBuilder('plugin_efo_config', $Config)
            ->setMethod('PUT')
            ->getForm();

        return $app->render('Efo/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function update(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $app['orm.em'];

        $em->beginTransaction();

        /** @var \Plugin\Efo\Repository\ConfigRepository $configRepository */
        $configRepository = $app['eccube.plugin.efo.repository.config'];

        /** @var \Plugin\Efo\Entity\Config $Config */
        $Config = $configRepository->find(1);

        /** @var \Symfony\Component\Form\FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $form = $formFactory->createBuilder('plugin_efo_config', $Config)
            ->setMethod('PUT')
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $em->rollback();

            return $app->render('Efo/Resource/template/admin/config.twig', array(
                'form' => $form->createView(),
            ));
        }

        $em->flush($Config);

        $em->commit();

        $app->addSuccess('設定を保存しました', 'admin');

        return $app->redirect($app->url('plugin_Efo_config'));
    }
}
