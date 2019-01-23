<?php

namespace Plugin\Efo\Controller\Admin\EntryForm;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\PageMax;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntryFormController extends AbstractController
{
    public function index(Application $app, Request $request)
    {
        /** @var FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $formBuilder = $formFactory->createNamedBuilder('search', 'plugin_efo_admin_entry_form_search')
            ->setMethod('get');

        $searchForm = $formBuilder->getForm();

        $disps = $app['eccube.repository.master.disp']->findAll();

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        $searchForm->submit($request->query->get($searchForm->getName()));

        $conditions = $searchForm->getData();

        /** @var \Plugin\Efo\Repository\EntryFormRepository $entryFormRepository */
        $entryFormRepository = $app['eccube.plugin.efo.repository.entry_form'];

        $queryBuilder = $entryFormRepository->createAdminSearchQueryBuilder($conditions);

        /** @var \Knp\Component\Pager\Paginator $paginator */
        $paginator = $app['paginator']();

        $max = array_reduce($pageMaxis, function ($max, PageMax $pageMax) {
            return max($max, $pageMax->getName());
        });

        $page_no = min(max($request->get('page_no', 1), 1), $max);

        $page_count = min(max($request->get('page_count', $app['config']['default_page_count']), 1), $max);

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];

        /* @var $softDeleteFilter \Eccube\Doctrine\Filter\SoftDeleteFilter */
        $softDeleteFilter = $entityManager->getFilters()->getFilter('soft_delete');
        $softDeleteFilter->setExcludes(array_merge($softDeleteFilter->getExcludes(), array(
            'Eccube\\Entity\\Product',
        )));

        /** @var \Knp\Component\Pager\Pagination\AbstractPagination $pagination */
        $pagination = $paginator->paginate($queryBuilder, $page_no, $page_count);

        return $app->render('Efo/Resource/template/admin/EntryForm/index.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'disps'      => $disps,
            'pageMaxis'  => $pageMaxis,
            'page_no'    => $pagination->getCurrentPageNumber(),
            'page_count' => $pagination->getItemNumberPerPage(),
        ));
    }

    public function create(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];

        /* @var $softDeleteFilter \Eccube\Doctrine\Filter\SoftDeleteFilter */
        $softDeleteFilter = $entityManager->getFilters()->getFilter('soft_delete');
        $softDeleteFilter->setExcludes(array_merge($softDeleteFilter->getExcludes(), array(
            'Eccube\\Entity\\Product',
        )));

        /** @var \Plugin\Efo\Service\EntryFormService $entryFormService */
        $entryFormService = $app['eccube.plugin.efo.service.entry_form'];

        $EntryForm = $entryFormService->newInstance();

        /** @var FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $formBuilder = $formFactory->createNamedBuilder('entry_form', 'plugin_efo_admin_entry_form', $EntryForm);

        $form = $formBuilder->getForm();

        return $app->render('Efo/Resource/template/admin/EntryForm/create.twig', array(
            'form'      => $form->createView(),
            'EntryForm' => $EntryForm,
        ));
    }

    public function store(Application $app, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];

        $entityManager->beginTransaction();

        /** @var \Plugin\Efo\Service\EntryFormService $entryFormService */
        $entryFormService = $app['eccube.plugin.efo.service.entry_form'];

        $EntryForm = $entryFormService->newInstance();

        /** @var FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $formBuilder = $formFactory
            ->createNamedBuilder('entry_form', 'plugin_efo_admin_entry_form', $EntryForm)
            ->setMethod('POST');

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $entityManager->rollback();

            return $app->render('Efo/Resource/template/admin/EntryForm/create.twig', array(
                'form'      => $form->createView(),
                'EntryForm' => $EntryForm,
            ));
        }

        $entryFormService->create($EntryForm);

        $entityManager->commit();

        $app->addSuccess('注文フォームを保存しました', 'admin');

        return $app->redirect($app->url('plugin_Efo_admin_entry_form_index'));
    }

    public function edit(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];

        /* @var $softDeleteFilter \Eccube\Doctrine\Filter\SoftDeleteFilter */
        $softDeleteFilter = $entityManager->getFilters()->getFilter('soft_delete');
        $softDeleteFilter->setExcludes(array_merge($softDeleteFilter->getExcludes(), array(
            'Eccube\\Entity\\Product',
        )));

        /** @var \Plugin\Efo\Repository\EntryFormRepository $entryFormRepository */
        $entryFormRepository = $app['eccube.plugin.efo.repository.entry_form'];

        $EntryForm = $entryFormRepository->find($id);

        if (!$EntryForm) {
            throw new NotFoundHttpException();
        }

        /** @var FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $formBuilder = $formFactory->createNamedBuilder('entry_form', 'plugin_efo_admin_entry_form', $EntryForm);

        $form = $formBuilder->getForm();

        return $app->render('Efo/Resource/template/admin/EntryForm/edit.twig', array(
            'form'      => $form->createView(),
            'EntryForm' => $EntryForm,
        ));
    }

    public function update(Application $app, Request $request, $id)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];

        $entityManager->beginTransaction();

        /** @var \Plugin\Efo\Repository\EntryFormRepository $entryFormRepository */
        $entryFormRepository = $app['eccube.plugin.efo.repository.entry_form'];

        /** @var \Plugin\Efo\Entity\EntryForm $EntryForm */
        $EntryForm = $entryFormRepository->find($id);

        if (!$EntryForm) {
            throw new NotFoundHttpException();
        }

        /** @var FormFactory $formFactory */
        $formFactory = $app['form.factory'];

        $formBuilder = $formFactory
            ->createNamedBuilder('entry_form', 'plugin_efo_admin_entry_form', $EntryForm)
            ->setMethod('PUT');

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $entityManager->rollback();

            return $app->render('Efo/Resource/template/admin/EntryForm/edit.twig', array(
                'form'      => $form->createView(),
                'EntryForm' => $EntryForm,
            ));
        }

        /** @var \Plugin\Efo\Service\EntryFormService $entryFormService */
        $entryFormService = $app['eccube.plugin.efo.service.entry_form'];

        $entryFormService->update($EntryForm);

        $entityManager->commit();

        $app->addSuccess('注文フォームを保存しました', 'admin');

        return $app->redirect($app->url('plugin_Efo_admin_entry_form_index'));
    }

    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        /** @var \Plugin\Efo\Repository\EntryFormRepository $entryFormRepository */
        $entryFormRepository = $app['eccube.plugin.efo.repository.entry_form'];

        /** @var \Plugin\Efo\Entity\EntryForm $EntryForm */
        $EntryForm = $entryFormRepository->find($id);

        if (!$EntryForm) {
            $app->deleteMessage();

            return $app->redirect($app->url('plugin_Efo_admin_entry_form_index'));
        }

        /** @var \Plugin\Efo\Service\EntryFormService $entryFormService */
        $entryFormService = $app['eccube.plugin.efo.service.entry_form'];

        $entryFormService->delete($EntryForm);

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];
        $entityManager->flush();

        $app->addSuccess('注文フォームを削除しました。', 'admin');

        return $app->redirect($app->url('plugin_Efo_admin_entry_form_index'));
    }
}
