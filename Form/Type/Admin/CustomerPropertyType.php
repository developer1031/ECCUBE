<?php

namespace Plugin\Efo\Form\Type\Admin;

use Eccube\Form\DataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerPropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', 'text', array(
                'label'       => '項目',
                'read_only'   => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => 255)),
                ),
            ))
            ->add('enabled', 'checkbox', array(
                'value'    => 1,
                'label'    => '表示',
                'required' => false,
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\\Plugin\\Efo\\Entity\\CustomerProperty',
        ));
    }

    public function getName()
    {
        return 'plugin_efo_admin_customer_property';
    }
}
