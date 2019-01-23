<?php
namespace Plugin\Efo\Controller\Front\Entry;

use Eccube\Application;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Validator\Constraints as Assert;

class EntryController extends \Eccube\Controller\EntryController
{
    public function index(Application $app, Request $request)
    {
        /** @var $Customer \Eccube\Entity\Customer */
        $Customer = $app['eccube.repository.customer']->newCustomer();

        /** @var \Eccube\Service\ShoppingService $shoppingService */
        $shoppingService = $app['eccube.service.shopping'];

        $Source = $app['eccube.service.shopping']->getNonMember('eccube.front.shopping.nonmember');

        if (!$Source) {
            /** @var \Eccube\Entity\Order $Order */
            $Order = $shoppingService->getOrder($app['config']['order_processing']);

            if ($Order && $Order->getCustomer()) {
                $Source = $Order->getCustomer();
            }
        }

        if ($Source) {
            $Customer
                ->setName01($Source->getName01())
                ->setName02($Source->getName02())
                ->setKana01($Source->getKana01())
                ->setKana02($Source->getKana02())
                ->setCompanyName($Source->getCompanyName())
                ->setEmail($Source->getEmail())
                ->setTel01($Source->getTel01())
                ->setTel02($Source->getTel02())
                ->setTel03($Source->getTel03())
                ->setZip01($Source->getZip01())
                ->setZip02($Source->getZip02())
                ->setZipcode($Source->getZipcode())
                ->setPref($Source->getPref())
                ->setAddr01($Source->getAddr01())
                ->setAddr02($Source->getAddr02());
        }

        /** @var \Plugin\Efo\Service\CustomerPropertyService $customerPropertyService */
        $customerPropertyService = $app['eccube.plugin.efo.service.customer_property'];

        $enabledProperties = $customerPropertyService->getEnabledPropertyNames();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('entry', $Customer);

        $event = new EventArgs(
            array(
                'builder'  => $builder,
                'Customer' => $Customer,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_ENTRY_INDEX_INITIALIZE, $event);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    $builder->setAttribute('freeze', true);
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $app->render('Entry/confirm.twig', array(
                        'form'              => $form->createView(),
                        'enabledProperties' => $enabledProperties,
                    ));

                case 'complete':
                    $Customer
                        ->setSalt(
                            $app['eccube.repository.customer']->createSalt(5)
                        )
                        ->setPassword(
                            $app['eccube.repository.customer']->encryptPassword($app, $Customer)
                        )
                        ->setSecretKey(
                            $app['eccube.repository.customer']->getUniqueSecretKey($app)
                        );

                    $CustomerAddress = new \Eccube\Entity\CustomerAddress();
                    $CustomerAddress
                        ->setFromCustomer($Customer);

                    $app['orm.em']->persist($Customer);
                    $app['orm.em']->persist($CustomerAddress);
                    $app['orm.em']->flush();

                    /** @var \Eccube\Entity\Order $Order */
                    $Order = $shoppingService->getOrder($app['config']['order_processing']);

                    if ($Order) {
                        $Order->setCustomer($Customer);

                        $app['orm.em']->flush();
                    }

                    $event = new EventArgs(
                        array(
                            'form'            => $form,
                            'Customer'        => $Customer,
                            'CustomerAddress' => $CustomerAddress,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_ENTRY_INDEX_COMPLETE, $event);

                    $activateUrl = $app->url('entry_activate', array('secret_key' => $Customer->getSecretKey()));

                    /** @var $BaseInfo \Eccube\Entity\BaseInfo */
                    $BaseInfo = $app['eccube.repository.base_info']->get();
                    $activateFlg = $BaseInfo->getOptionCustomerActivate();

                    // 仮会員設定が有効な場合は、確認メールを送信し完了画面表示.
                    if ($activateFlg) {
                        // メール送信
                        $app['eccube.service.mail']->sendCustomerConfirmMail($Customer, $activateUrl);

                        if ($event->hasResponse()) {
                            return $event->getResponse();
                        }

                        return $app->redirect($app->url('shopping'));
                        // 仮会員設定が無効な場合は認証URLへ遷移させ、会員登録を完了させる.
                    } else {
                        return $app->redirect($activateUrl);
                    }
            }
        }

        return $app->render('Entry/index.twig', array(
            'form'              => $form->createView(),
            'enabledProperties' => $enabledProperties,
        ));
    }

    public function activate(Application $app, Request $request, $secret_key)
    {
        parent::activate($app, $request, $secret_key);

        return $app->redirect($app->url('shopping'));
    }
}
