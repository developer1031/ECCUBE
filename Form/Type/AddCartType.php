<?php
namespace Plugin\Efo\Form\Type;

use Eccube\Entity\ClassCategory;
use Eccube\Entity\ProductClass;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class AddCartType extends \Eccube\Form\Type\AddCartType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $Product \Eccube\Entity\Product */
        $Product = $options['product'];
        $this->Product = $Product;
        $ProductClasses = $Product->getProductClasses();

        $builder
            ->add('mode', 'hidden', array(
                'data' => 'add_cart',
            ))
            ->add('product_id', 'hidden', array(
                'data'        => $Product->getId(),
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array('pattern' => '/^\d+$/')),
                ),
            ))
            ->add('product_class_id', 'hidden', array(
                'data'        => count($ProductClasses) === 1 ? $ProductClasses[0]->getId() : '',
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^\d+$/')),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());

        if ($Product->getStockFind()) {
            $builder
                ->add('quantity', 'integer', array(
                    'data'        => 1,
                    'attr'        => array(
                        'min'       => 1,
                        'maxlength' => $this->config['int_len'],
                    ),
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\GreaterThanOrEqual(array(
                            'value' => 1,
                        )),
                        new Assert\Regex(array('pattern' => '/^\d+$/')),
                    ),
                ));

            if ($Product && $Product->getProductClasses()) {
                if ($Product->getClassName1()) {
                    /** @var array $classCategories1 */
                    $classCategories1 = $Product->getClassCategories1();

                    $keys = $Product
                        ->getProductClasses()
                        ->filter(function (ProductClass $pc) use ($classCategories1) {
                            return array_key_exists($pc->getClassCategory1()->getId(), $classCategories1);
                        })
                        ->map(function (ProductClass $pc) {
                            return $pc->getClassCategory1();
                        })
                        ->getValues();
                    $keys = array_reduce($keys,
                        function (array $keys, ClassCategory $cc) {
                            $keys[$cc->getId()] = $cc->getRank();

                            return $keys;
                        },
                        array());

                    uksort($classCategories1, function ($a, $b) use ($keys) {
                        return $keys[$b] - $keys[$a];
                    });

                    $builder->add('classcategory_id1', 'choice', array(
                        'label'    => $Product->getClassName1(),
                        'choices'  => $classCategories1,
                        'expanded' => false,
                    ));
                }
                if ($Product->getClassName2()) {
                    $builder->add('classcategory_id2', 'choice', array(
                        'label'   => $Product->getClassName2(),
                        'choices' => array('__unselected' => '選択してください'),
                    ));
                }
            }

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($Product) {
                $data = $event->getData();
                $form = $event->getForm();
                if ($Product->getClassName2()) {
                    if ($data['classcategory_id1']) {
                        $form->add('classcategory_id2', 'choice', array(
                            'label'   => $Product->getClassName2(),
                            'choices' => array('__unselected' => '選択してください') + $Product->getClassCategories2($data['classcategory_id1']),
                        ));
                    }
                }
            });
        }
    }

    public function getName()
    {
        return 'efo_add_cart';
    }
}
