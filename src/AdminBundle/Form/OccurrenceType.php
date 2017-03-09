<?php

namespace AdminBundle\Form;

use AppBundle\Entity\Place;
use JavierEguiluz\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 *
 */
class OccurrenceType extends AbstractType {
  /** @var \Symfony\Component\Translation\TranslatorInterface */
  private $translator;

  public function __construct(TranslatorInterface $translator) {
    $this->translator = $translator;
  }

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options) {
    $placeholder = [];
    foreach (['year', 'month', 'day', 'hour', 'minute'] as $key) {
      $placeholder[$key] = 'form.type.occurrence.datetime.placeholder.' . $key;
    }

    $builder
      ->add('startDate', DateTimeType::class, [
        'placeholder' => $placeholder,
        // 'widget' => 'single_text',
        // 'html5' => false,
        'attr' => [
          'help' => __METHOD__,
        ],
      ])
      ->add('endDate', DateTimeType::class, [
        'placeholder' => $placeholder,
        'attr' => [
          // 'easyadmin' => ['help' => __METHOD__],
        ],
      ])
      ->add('place', EasyAdminAutocompleteType::class, [
        'class' => Place::class,
      ]);
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
