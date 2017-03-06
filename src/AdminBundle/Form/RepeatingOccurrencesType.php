<?php

namespace AdminBundle\Form;

use AppBundle\Entity\Place;
use Doctrine\ORM\EntityRepository;
use JavierEguiluz\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 *
 */
class RepeatingOccurrencesType extends AbstractType {
  /**
   * @var \Doctrine\ORM\EntityManagerInterface
   */
  private $placeRepository;

  public function __construct(EntityRepository $placeRepository) {
    $this->placeRepository = $placeRepository;
  }

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
      ]);
    for ($day = 1; $day <= 7; $day++) {
      $builder
        ->add('start_time_' . $day, TimeType::class, [
          'placeholder' => ['hour' => '', 'minute' => ''],
        ])
        ->add('end_time_' . $day, TimeType::class, [
          'placeholder' => ['hour' => '', 'minute' => ''],
        ]);
    }
    $builder->add('update_repeating_occurrences', SubmitType::class, [
      'label' => 'Update repeating occurrences',
      'attr' => [
        'onclick' => 'return typeof(confirmCreateRepeatingOccurrences) === "undefined" || confirmCreateRepeatingOccurrences(this.form)',
      ],
    ]);

    $builder
      // This is needed to make 'place' work as expected. I don't really know why …
      ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        $data = $event->getData();
        if (isset($data['place'])) {
          $data['place'] = $this->placeRepository->find($data['place']);
          $event->setData($data);
        }
      })
      ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
        $form = $event->getForm();
        $parent = $form->getParent();
        if ($parent) {
          if ($form->get('update_repeating_occurrences')->isClicked()) {
            $parent->remove('occurrences');
          }
        } else {
          $parent->remove($form->getName());
        }
      });
  }

  /**
   * @return string
   */
  public function getName() {
    return 'adminbundle_repeating_occurrences';
  }

}
