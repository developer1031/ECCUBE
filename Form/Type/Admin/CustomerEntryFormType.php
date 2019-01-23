<?php

namespace Plugin\Efo\Form\Type\Admin;

use Eccube\Form\DataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerEntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('properties', 'collection', array(
                'type'    => 'plugin_efo_admin_customer_property',
                'options' => array(),
            ))
            ->add('shopping_login_destination', 'choice', array(
                'label'       => '非ログイン状態での注文手続き',
                'required'    => true,
                'choices'     => array(
                    0 => 'ログイン/新規会員登録するかゲスト購入するかを確認する',
                    1 => '新規会員登録へ遷移する',
                    2 => 'ゲスト購入へ遷移する',
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    public function getName()
    {
        return 'plugin_efo_admin_customer_entry_form';
    }
}
