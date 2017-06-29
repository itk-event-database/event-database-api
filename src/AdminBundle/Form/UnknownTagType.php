<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 */
class UnknownTagType extends AbstractType
{

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name')
        ->add('tag');
    }

  /**
   * @param OptionsResolverInterface $resolver
   */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
          'data_class' => 'AppBundle\Entity\UnknownTag'
        ]);
    }

  /**
   * @return string
   */
    public function getName()
    {
        return 'adminbundle_unknown_tag';
    }
}
