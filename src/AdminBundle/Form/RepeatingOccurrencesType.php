<?php

namespace AdminBundle\Form;

use AppBundle\Entity\Place;
use JavierEguiluz\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 */
class RepeatingOccurrencesType extends AbstractType {

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder
      ->add('place', EasyAdminAutocompleteType::class, [
        'class' => Place::class,
        'required' => false,
      ])

      ->add('start_day', DateType::class, [
        'placeholder' => ['year' => '', 'month' => '', 'day' => ''],
      ])

      ->add('end_day', DateType::class, [
        'placeholder' => ['year' => '', 'month' => '', 'day' => ''],
      ])

      ->add('monday_start_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ->add('monday_end_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])

      ->add('tuesday_start_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ->add('tuesday_end_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])

      ->add('wednesday_start_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ->add('wednesday_end_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])

      ->add('thursday_start_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ->add('thursday_end_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])

      ->add('friday_start_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ->add('friday_end_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])

      ->add('saturday_start_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ->add('saturday_end_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])

      ->add('sunday_start_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ->add('sunday_end_time', TimeType::class, [
        'placeholder' => ['hour' => '', 'minute' => ''],
      ])
      ;
  }

  /**
   * @param OptionsResolverInterface $resolver
   */
  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults([
      // 'data_class' => 'array',
    ]);
  }

  /**
   * @return string
   */
  public function getName() {
    return 'adminbundle_repeating_occurrences';
  }

}
