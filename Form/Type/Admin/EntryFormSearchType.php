<?php

namespace Plugin\Efo\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EntryFormSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('keyword', 'text', array(
                'required' => false,
            ))
            ->add('create_date_start', 'date', array(
                'label'       => '作成日(FROM)',
                'required'    => false,
                'input'       => 'datetime',
                'widget'      => 'single_text',
                'format'      => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('create_date_end', 'date', array(
                'label'       => '作成日(TO)',
                'required'    => false,
                'input'       => 'datetime',
                'widget'      => 'single_text',
                'format'      => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_start', 'date', array(
                'label'       => '更新日(FROM)',
                'required'    => false,
                'input'       => 'datetime',
                'widget'      => 'single_text',
                'format'      => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_end', 'date', array(
                'label'       => '更新日(TO)',
                'required'    => false,
                'input'       => 'datetime',
                'widget'      => 'single_text',
                'format'      => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());;
    }

    public function getName()
    {
        return 'plugin_efo_admin_entry_form_search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
