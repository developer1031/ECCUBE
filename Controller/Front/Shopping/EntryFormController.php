<?php

namespace Plugin\Efo\Controller\Front\Shopping;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Cart;
use Eccube\Entity\CartItem;
use Eccube\Entity\Customer;
use Eccube\Entity\CustomerAddress;
use Eccube\Entity\DeliveryTime;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Exception\CartException;
use Eccube\Service\ShoppingService;
use Silex\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntryFormController extends AbstractController
{
    /**
     * @var string 非会員用セッションキー
     */
    private $sessionKey = 'eccube.front.shopping.nonmember';

    /**
     * @var string 非会員用セッションキー
     */
    private $sessionCustomerAddressKey = 'eccube.front.shopping.nonmember.customeraddress';

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Application $app, Request $request)
    {
//        /** @var \Plugin\Efo\Service\ConfigService $configService */
//        $configService = $app['eccube.plugin.efo.service.config'];
//
//        $Config = $configService->get();

        /** @var \Eccube\Repository\DeliveryFeeRepository $deliveryFeeRepository */
        $deliveryFeeRepository = $app['eccube.repository.delivery_fee'];

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $app['session'];

        /** @var \Symfony\Component\Routing\RouteCollection $routes */
        $routes = $app['routes'];

        /** @var \Silex\Route $route */
        $route = $routes->get($request->get('_route'));

        /** @var \Plugin\Efo\Service\EntryFormService $entryFormService */
        $entryFormService = $app['eccube.plugin.efo.service.entry_form'];
        $EntryForm = $entryFormService->findByPath($route->getPath());

        /** @var \Eccube\Service\ShoppingService $shoppingService */
        $shoppingService = $app['eccube.service.shopping'];

        /** @var \Eccube\Repository\CustomerFavoriteProductRepository $customerFavoriteProductRepository */
        $customerFavoriteProductRepository = $app['eccube.repository.customer_favorite_product'];

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $app['orm.em'];

        $Product = $EntryForm->getProduct();

        if (!$request->getSession()->has('_security_admin') && $Product->getStatus()->getId() !== 1) {
            throw new NotFoundHttpException();
        }

        if (count($Product->getProductClasses()) < 1) {
            throw new NotFoundHttpException();
        }

        /** @var \Eccube\Service\CartService $cartService */
        $cartService = $app['eccube.service.cart'];
        $cartService->getCart()->setLock(false);

        if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
            $Customer = $app->user();
        } elseif (!($Customer = $shoppingService->getNonMember($this->sessionKey))) {
            $Customer = $this->buildCustomer($app);
        }

        /** @var \Doctrine\Common\Collections\Collection $cartItems */
        $cartItems = $cartService->getCart()->getCartItems();

        if ($request->getMethod() === 'GET') {
            $needsRefresh = array_reduce($cartItems->toArray(), function ($result, CartItem $cartItem) use ($Product) {
                return $result && $cartItem->getObject()->getProduct()->getId() != $Product->getId();
            }, true);

            if ($needsRefresh) {
                $Product->_calc();

                $ProductClasses = $Product
                    ->getProductClasses()
                    ->filter(function (ProductClass $ProductClass) {
                        return $ProductClass->getStockUnlimited() == Constant::ENABLED || $ProductClass->getStock() > 0;
                    })
                    ->getValues();

                usort($ProductClasses, function (ProductClass $a, ProductClass $b) {
                    return $b->getClassCategory1()->getRank() - $a->getClassCategory1()->getRank();
                });

                /** @var \Eccube\Entity\ProductClass $ProductClass */
                $ProductClass = reset($ProductClasses);

                $cartService
                    ->clear()
                    ->addProduct($ProductClass->getId(), 1);
            }
        }

        $Order = $shoppingService->getOrder($app['config']['order_processing']);

        if (is_null($Order)) {
            $autoCommit = $entityManager->getConnection()->isAutoCommit();
            $entityManager->getConnection()->setAutoCommit(true);
            $entityManager->beginTransaction();
            $Order = $shoppingService->createOrder($Customer);
            $entityManager->flush();
            $entityManager->rollback();
            $entityManager->getConnection()->setAutoCommit($autoCommit);
        }

        if ($request->getMethod() === 'GET') {
            $defaults = $this->buildOrderFormDefaults($Customer, $Product, $cartService->getCart());
        } else {
            $defaults = null;
        }

        /** @var \Symfony\Component\Form\FormFactoryInterface $formFactory */
        $formFactory = $app['form.factory'];
        $form = $formFactory
            ->createNamedBuilder('', 'plugin_efo_order_form', $defaults, array(
                'product'          => $Product,
                'order'            => $Order,
                'shopping_service' => $shoppingService,
                'csrf_protection'  => false,
            ))
            ->getForm();

        /* @var \Eccube\Repository\DeliveryTimeRepository $deliveryTimeRepository */
        $deliveryTimeRepository = $app['eccube.repository.delivery_time'];

        $deliveryTimes = array_reduce($deliveryTimeRepository->findAll(), function (array $group, DeliveryTime $deliveryTime) {
            /** @var \Eccube\Entity\Delivery $delivery */
            $delivery = $deliveryTime->getDelivery();

            $group[$delivery->getId()][] = array(
                'id'    => $deliveryTime->getId(),
                'label' => $deliveryTime->getDeliveryTime(),
            );

            return $group;
        }, array());

        $loggedIn = $app->isGranted('ROLE_USER');

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var \Eccube\Repository\BaseInfoRepository $baseInfoRepository */
                $baseInfoRepository = $app['eccube.repository.base_info'];

                try {
                    $entityManager->beginTransaction();
                    $data = $form->getData();

                    $Customer = $this->buildCustomer($app, $data['nonmember']);

                    $addCartData = $data['add_cart'];
                    $cartService->clear();
                    $cartService->addProduct($addCartData['product_class_id'], $addCartData['quantity'])->save();

                    $Order = $shoppingService->getOrder($app['config']['order_processing']);

                    if (is_null($Order)) {
                        $Order = $shoppingService->createOrder($Customer);
                    }

                    if ($Customer->getId() === null) {
                        // 非会員用セッションを作成
                        $nonMember = array();
                        $nonMember['customer'] = $Customer;
                        $nonMember['pref'] = $Customer->getPref()->getId();

                        $session->set($this->sessionKey, $nonMember);

                        $customerAddresses = array();
                        $customerAddresses[] = $Customer->getCustomerAddresses()->first();
                        $session->set($this->sessionCustomerAddressKey, serialize($customerAddresses));
                    } else {
                        $session->remove($this->sessionKey);
                        $session->remove($this->sessionCustomerAddressKey);
                    }

                    /** @var \Doctrine\ORM\PersistentCollection $shippings */
                    $shippings = $data['shippings'];

                    /** @var \Eccube\Entity\Shipping $Shipping */
                    foreach ($Order->getShippings() as $i => $Shipping) {
                        $s = $shippings->get($i);

                        $Shipping
                            ->setName01($Customer->getName01())
                            ->setName02($Customer->getName02())
                            ->setKana01($Customer->getKana01())
                            ->setKana02($Customer->getKana02())
                            ->setCompanyName($Customer->getCompanyName())
                            ->setTel01($Customer->getTel01())
                            ->setTel02($Customer->getTel02())
                            ->setTel03($Customer->getTel03())
                            ->setFax01($Customer->getFax01())
                            ->setFax02($Customer->getFax02())
                            ->setFax03($Customer->getFax03())
                            ->setZip01($Customer->getZip01())
                            ->setZip02($Customer->getZip02())
                            ->setZipcode($Customer->getZip01() . $Customer->getZip02())
                            ->setPref($Customer->getPref())
                            ->setAddr01($Customer->getAddr01())
                            ->setAddr02($Customer->getAddr02())
                            ->setDelivery($s->getDelivery())
                            ->setDeliveryTime($s->getDeliveryTime())
                            ->setShippingDeliveryDate($s->getShippingDeliveryDate());

                        $shoppingService->setShippingDeliveryFee($Shipping);
                    }

                    // 支払い情報をセット
                    $payment = $data['shopping']['payment'];
                    $message = $data['shopping']['message'];

                    $Order->setPayment($payment);
                    $Order->setPaymentMethod($payment->getMethod());
                    $Order->setMessage($message);
                    $Order->setCharge($payment->getCharge());

                    $shoppingService->getAmount($Order);

                    $entityManager->flush();
                    $entityManager->commit();

                    $cartService->lock();
                    $cartService->setPreOrderId($Order->getPreOrderId());
                    $cartService->save();

                    if ($form->get('submit_with_entry')->isClicked()) {
                        return $app->redirect($app->url('entry'));
                    }

                    return $app->redirect($app->url('shopping'));
                } catch (CartException $e) {
                    $app->addRequestError($e->getMessage());
                }
            }
        }

        if ($app->isGranted('ROLE_USER')) {
            $is_favorite = $customerFavoriteProductRepository->isFavorite($Customer, $Product);
        } else {
            $is_favorite = false;
        }

        /** @var \Plugin\Efo\Service\CustomerPropertyService $customerPropertyService */
        $customerPropertyService = $app['eccube.plugin.efo.service.customer_property'];

        $enabledProperties = $customerPropertyService->getEnabledPropertyNames();

        return $app->render($EntryForm->getPageLayout()->getFileName() . '.twig', array(
            'title'                 => '',
            'subtitle'              => $Product->getName(),
            'form'                  => $form->createView(),
            'Product'               => $Product,
            'is_favorite'           => $is_favorite,
            'Order'                 => $Order,
            'deliveryTimes'         => $deliveryTimes,
            'defaults'              => $defaults,
            'loggedIn'              => $loggedIn,
            'EntryForm'             => $EntryForm,
            'enabledProperties'     => $enabledProperties,
        ));
    }

    /**
     * @param \Eccube\Application $app
     * @param array $data
     * @return \Eccube\Entity\Customer
     */
    protected function buildCustomer(Application $app, array $data = null)
    {
        /** @var \Eccube\Repository\Master\PrefRepository $prefRepository */
        $prefRepository = $app['eccube.repository.master.pref'];

        /** @var \Eccube\Repository\CustomerRepository $customerRepository */
        $customerRepository = $app['eccube.repository.customer'];

        $data = $data
            ?: array(
                'name01'       => null,
                'name02'       => null,
                'kana01'       => null,
                'kana02'       => null,
                'company_name' => null,
                'email'        => null,
                'tel01'        => null,
                'tel02'        => null,
                'tel03'        => null,
                'zip01'        => null,
                'zip02'        => null,
                'pref'         => $prefRepository->find(1),
                'addr01'       => null,
                'addr02'       => null,
            );

        $Customer = $app->isGranted('ROLE_USER') ? $app->user() : $customerRepository->newCustomer();

        $Customer
            ->setName01($data['name01'])
            ->setName02($data['name02'])
            ->setKana01($data['kana01'])
            ->setKana02($data['kana02'])
            ->setCompanyName($data['company_name'])
            ->setEmail($data['email'])
            ->setTel01($data['tel01'])
            ->setTel02($data['tel02'])
            ->setTel03($data['tel03'])
            ->setZip01($data['zip01'])
            ->setZip02($data['zip02'])
            ->setZipcode($data['zip01'] . $data['zip02'])
            ->setPref($data['pref'])
            ->setAddr01($data['addr01'])
            ->setAddr02($data['addr02']);

        if ($Customer->getId() === null) {
            $CustomerAddress = new CustomerAddress();
            $CustomerAddress
                ->setCustomer($Customer)
                ->setName01($data['name01'])
                ->setName02($data['name02'])
                ->setKana01($data['kana01'])
                ->setKana02($data['kana02'])
                ->setCompanyName($data['company_name'])
                ->setTel01($data['tel01'])
                ->setTel02($data['tel02'])
                ->setTel03($data['tel03'])
                ->setZip01($data['zip01'])
                ->setZip02($data['zip02'])
                ->setZipcode($data['zip01'] . $data['zip02'])
                ->setPref($data['pref'])
                ->setAddr01($data['addr01'])
                ->setAddr02($data['addr02'])
                ->setDelFlg(Constant::DISABLED);

            $Customer->addCustomerAddress($CustomerAddress);
        }

        return $Customer;
    }

    protected function buildOrderFormDefaults(Customer $Customer, Product $product, Cart $cart)
    {
        $addCart = array();

        if (count($cart->getCartItems()) > 0) {
            /** @var \Doctrine\Common\Collections\Collection $cartItems */
            $cartItems = $cart->getCartItems();

            /** @var \Eccube\Entity\CartItem $cartItem */
            $cartItem = $cartItems->first();

            $productClass = $cartItem->getObject();

            if ($productClass->getProduct()->getId() == $product->getId()) {
                $addCart['quantity'] = $cartItem->getQuantity();
                $addCart['product_id'] = $productClass->getProduct()->getId();
                $addCart['product_class_id'] = $productClass->getId();

                if ($productClass->hasClassCategory1()) {
                    $addCart['classcategory_id1'] = $productClass->getClassCategory1()->getId();
                }

                if ($productClass->hasClassCategory2()) {
                    $addCart['classcategory_id2'] = $productClass->getClassCategory2()->getId();
                }
            }
        }

        return array(
            'nonmember' => $Customer,
            'add_cart'  => $addCart,
        );
    }

    /**
     * @param \Eccube\Service\ShoppingService $shoppingService
     * @return array|null
     */
    protected function loadRememberedCustomer(ShoppingService $shoppingService)
    {
        /** @var \Eccube\Entity\Customer $Customer */
        $Customer = $shoppingService->getNonMember($this->sessionKey);

        if (!$Customer) {
            return null;
        }

        return array(
            'name01'       => $Customer->getName01(),
            'name02'       => $Customer->getName02(),
            'kana01'       => $Customer->getKana01(),
            'kana02'       => $Customer->getKana02(),
            'company_name' => $Customer->getCompanyName(),
            'tel01'        => $Customer->getTel01(),
            'tel02'        => $Customer->getTel02(),
            'tel03'        => $Customer->getTel03(),
            'fax01'        => $Customer->getFax01(),
            'fax02'        => $Customer->getFax02(),
            'fax03'        => $Customer->getFax03(),
            'zip01'        => $Customer->getZip01(),
            'zip02'        => $Customer->getZip02(),
            'pref'         => $Customer->getPref()->getId(),
            'addr01'       => $Customer->getAddr01(),
            'addr02'       => $Customer->getAddr02(),
            'email'        => $Customer->getEmail(),
        );
    }
}
