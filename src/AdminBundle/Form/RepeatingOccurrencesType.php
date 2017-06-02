<?php

namespace AdminBundle\Form;

use AppBundle\Entity\Place;
use Doctrine\ORM\EntityRepository;
use JavierEguiluz\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        'required' => FALSE,
      ])
      ->add('start_day', DateType::class, [
        'placeholder' => ['year' => 'form.type.occurrence.datetime.placeholder.year', 'month' => 'form.type.occurrence.datetime.placeholder.month', 'day' => 'form.type.occurrence.datetime.placeholder.day'],
        'model_timezone' => 'UTC',
        'view_timezone' => $options['view_timezone'],
      ])
      ->add('end_day', DateType::class, [
        'placeholder' => ['year' => 'form.type.occurrence.datetime.placeholder.year', 'month' => 'form.type.occurrence.datetime.placeholder.month', 'day' => 'form.type.occurrence.datetime.placeholder.day'],
        'model_timezone' => 'UTC',
        'view_timezone' => $options['view_timezone'],
      ])
      ->add('ticket_price_range', PriceRangeType::class);

    for ($day = 1; $day <= 7; $day++) {
      $builder
        ->add('start_time_' . $day, TimeType::class, [
          'placeholder' => ['hour' => 'form.type.occurrence.datetime.placeholder.hour', 'minute' => 'form.type.occurrence.datetime.placeholder.minute'],
        ])
        ->add('end_time_' . $day, TimeType::class, [
          'placeholder' => ['hour' => 'form.type.occurrence.datetime.placeholder.hour', 'minute' => 'form.type.occurrence.datetime.placeholder.minute'],
        ]);
    }

    // A pseudo field used only for general error messages.
    $builder->add('message', TextType::class, [
      'label_attr' => [
        'class' => 'hidden',
      ],
      'attr' => [
        'class' => 'hidden',
      ],
    ]);

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
            // We don't want to submit occurrences when (re)creating repeating occurrences.
            $parent->remove('occurrences');
            $this->validateRepeatingOccurrences($event);
          }
        }
        else {
          // We don't want to update repeating occurrences data when not creating repeating occurrences.
          $parent->remove($form->getName());
        }
      });
  }

  private function validateRepeatingOccurrences(FormEvent $event) {
    $form = $event->getForm();
    $place = $form->get('place');
    if (!$place->getData()) {
      $place->addError(new FormError('Please select a place'));
    }
    $startDay = $form->get('start_day');
    if (!$startDay->getData()) {
      $startDay->addError(new FormError('Please specify a start day'));
    }
    $endDay = $form->get('end_day');
    if (!$endDay->getData()) {
      $endDay->addError(new FormError('Please specify an end day'));
    }
    if ($startDay->getData() && $endDay->getData() && $endDay->getData() < $startDay->getData()) {
      $endDay->addError(new FormError('End day must be after start day'));
    }
    $ticketPriceRange = $form->get('ticket_price_range');
    if ($ticketPriceRange->getData() === NULL) {
      $ticketPriceRange->addError(new FormError('Please specify ticket price'));
    }

    $numberOfTimeIntervals = 0;
    for ($day = 1; $day <= 7; $day++) {
      $startTime = $form->get('start_time_' . $day);
      $endTime = $form->get('end_time_' . $day);

      if ($startTime->getData() && !$endTime->getData()) {
        $endTime->addError(new FormError('Please specify an end time'));
      }
      elseif (!$startTime->getData() && $endTime->getData()) {
        $startTime->addError(new FormError('Please specify a start time'));
      }
      elseif ($startTime->getData() && $endTime->getData() && $endTime->getData() <= $startTime->getData()) {
        $endTime->addError(new FormError('End time must be after start time'));
      }
      elseif ($startTime->getData() && $endTime->getData()) {
        $numberOfTimeIntervals++;
      }
    }
    if ($numberOfTimeIntervals === 0) {
      $form->get('message')->addError(new FormError('Please specify at least one time interval'));
    }
  }

  /**
   * @param OptionsResolverInterface $resolver
   */
  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults([
      'view_timezone' => 'GMT',
    ]);
  }

  /**
   * @return string
   */
  public function getName() {
    return 'adminbundle_repeating_occurrences';
  }

}
