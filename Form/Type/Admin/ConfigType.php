<?php
namespace Plugin\Efo\Form\Type\Admin;

use Eccube\Event\FormEventSubscriber;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber(new FormEventSubscriber());

        return $builder;
    }

    public function getName()
    {
        return 'plugin_efo_config';
    }
}
