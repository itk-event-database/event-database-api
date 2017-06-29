<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 */
class FeedType extends AbstractType
{

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name')
        ->add('configuration', TextType::class);

        $builder->get('configuration')
        ->addModelTransformer(new CallbackTransformer(
            function ($configuration) {
                    // Transform the array to a string.
                    return is_array($configuration) ? json_encode($configuration) : '';
            },
            function ($configuration) {
                    // Transform the string back to an array.
                    return json_decode($configuration, true);
            }
        ));
    }

  /**
   * @param OptionsResolverInterface $resolver
   */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
          'data_class' => 'AdminBundle\Entity\Feed'
        ]);
    }

  /**
   * @return string
   */
    public function getName()
    {
        return 'adminbundle_feed';
    }
}
