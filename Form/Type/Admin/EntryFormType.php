<?php

namespace Plugin\Efo\Form\Type\Admin;

use Eccube\Entity\Product;
use Eccube\Form\DataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label'       => '注文フォーム名',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => 255)),
                ),
            ))
            ->add('path', 'text', array(
                'required'    => false,
                'label'       => 'パス',
                'constraints' => array(
                    new Assert\Length(array('max' => 1024)),
                ),
            ))
            ->add('Product', 'entity', array(
                'label'        => '商品',
                'class'        => 'Eccube\\Entity\\Product',
                'property'     => 'name',
                'choice_label' => function (Product $Product) {
                    return $Product->getName();
                },
            ))
            ->add('customer_registration_enabled', 'checkbox', array(
                'value'    => 1,
                'label'    => '表示する',
                'required' => false,
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());

        $builder
            ->get('path')
            ->addModelTransformer(new CallbackTransformer(
                function ($value) {
                    return ltrim($value, '/');
                },
                function ($value) {
                    return '/' . ltrim($value, '/');
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\\Plugin\\Efo\\Entity\\EntryForm',
        ));
    }

    public function getName()
    {
        return 'plugin_efo_admin_entry_form';
    }
}
