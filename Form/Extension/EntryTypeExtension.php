<?php
namespace Plugin\Efo\Form\Extension;

use Silex\Application;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class EntryTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Eccube\Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getExtendedType()
    {
        return 'entry';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // $builder->remove('fax');
    }
}
