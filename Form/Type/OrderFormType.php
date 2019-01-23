<?php

namespace Plugin\Efo\Form\Type;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class OrderFormType extends AbstractType
{
    /**
     * @var \Eccube\Application
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Eccube\Entity\Order $Order */
        $Order = $options['order'];

        /** @var \Eccube\Service\ShoppingService $ShoppingService */
        $ShoppingService = $options['shopping_service'];

        $message = $Order->getMessage();

        $deliveries = $ShoppingService->getDeliveriesOrder($Order);

        $payments = $ShoppingService->getFormPayments($deliveries, $Order);

        $builder
            ->add('add_cart', 'efo_add_cart', array(
                'product'           => $options['product'],
                'id_add_product_id' => $options['id_add_product_id'],
                'required'          => false,
                'label'             => false,
            ))
            ->add('nonmember', 'nonmember', array(
                'required' => false,
                'label'    => false,
            ))
            ->add('shopping', 'shopping', array(
                'payments' => $payments,
                'payment'  => $Order->getPayment(),
                'message'  => $message,
            ))
            ->add('shippings', 'collection', array(
                'type' => 'shipping_item',
                'data' => $Order->getShippings(),
            ))
            ->add('submit', 'submit', array(
            ))
            ->add('submit_with_entry', 'submit', array())
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());

        return $builder->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired('product');
        $resolver->setRequired('order');
        $resolver->setRequired('shopping_service');

        $resolver->setDefaults(array(
            'id_add_product_id' => false,
        ));
    }

    public function getName()
    {
        return 'plugin_efo_order_form';
    }
}
