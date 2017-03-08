<?php

namespace AdminBundle\Form;

use AppBundle\Entity\Place;
use JavierEguiluz\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 */
class OccurrenceType extends AbstractType {

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder
      ->add('startDate', DateTimeType::class, [
        'placeholder' => ['year' => '', 'month' => '', 'day' => '', 'hour' => '', 'minute' => ''],
        // 'widget' => 'single_text',
        // 'html5' => false,
      ])
      ->add('endDate', DateTimeType::class, [
        'placeholder' => ['year' => '', 'month' => '', 'day' => '', 'hour' => '', 'minute' => ''],
        'attr' => [
          // 'easyadmin' => [],
        ],
      ])
      ->add('place', EasyAdminAutocompleteType::class, [ 'class' => Place::class, ])
      ;
  }

  /**
   * @param OptionsResolverInterface $resolver
   */
  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults([
          'data_class' => 'AppBundle\Entity\Occurrence'
      ]);
  }

  /**
   * @return string
   */
  public function getName() {
    return 'adminbundle_occurrence';
  }

}
