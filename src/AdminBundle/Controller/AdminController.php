<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\Place;
use Doctrine\Common\Collections\ArrayCollection;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class AdminController extends BaseAdminController {
  public function cloneEventAction() {
    $id = $this->request->query->get('id');
    $event = $this->em->getRepository('AppBundle:Event')->find($id);
    if ($event) {
      $clone = clone $event;
      $this->em->persist($clone);
      $this->em->flush();

      return $this->redirectToRoute('easyadmin', array(
        'action' => 'edit',
        'id' => $clone->getId(),
        'entity' => $this->request->query->get('entity'),
      ));
    }

    $refererUrl = $this->request->query->get('referer', '');

    return !empty($refererUrl)
      ? $this->redirect(urldecode($refererUrl))
      : $this->redirectToRoute('easyadmin', array(
        'action' => 'list',
        'entity' => $this->request->query->get('entity'),
      ));
  }

  // @see https://github.com/javiereguiluz/EasyAdminBundle/blob/master/Resources/doc/tutorials/fosuserbundle-integration.md
  public function createNewUserEntity() {
    return $this->get('fos_user.user_manager')->createUser();
  }

  public function prePersistUserEntity($user) {
    $this->get('fos_user.user_manager')->updateUser($user, false);
  }

  public function preUpdateUserEntity($user) {
    $this->get('fos_user.user_manager')->updateUser($user, false);
  }

  public function prePersistEventEntity(Event $event) {
    $this->handleRepeatingOccurrences($event);
  }

  public function preUpdateEventEntity(Event $event) {
    $this->handleRepeatingOccurrences($event);
  }

  private function handleRepeatingOccurrences(Event $event) {
    // Check that 'update_repeating_occurrences' submit button has been clicked.
    // @TODO: There must be a better way to do this.
    $values = $this->request->request->all();
    if (isset($values['event'], $values['event']['repeating_occurrences'], $values['event']['repeating_occurrences']['update_repeating_occurrences'])) {
      $repeatingOccurrences = $event->getRepeatingOccurrences();
      if ($repeatingOccurrences) {
        /** @var Place $place */
        $place = isset($repeatingOccurrences['place']) ? $repeatingOccurrences['place'] : null;
        /** @var \DateTime $startDay */
        $startDay = isset($repeatingOccurrences['start_day']) ? clone $repeatingOccurrences['start_day'] : null;
        /** @var \DateTime $endDay */
        $endDay = isset($repeatingOccurrences['end_day']) ? clone $repeatingOccurrences['end_day'] : null;

        if ($place && $startDay && $endDay && $startDay < $endDay) {
          $occurrences = new ArrayCollection();

          $startDay->setTime(0, 0, 0);
          $endDay->setTime(0, 0, 0);
          while ($startDay <= $endDay) {
            $day = $startDay->format('N');
            $startTime = $repeatingOccurrences['start_time_' . $day];
            $endTime = $repeatingOccurrences['end_time_' . $day];
            if ($startTime && $endTime) {
              $occurrence = new Occurrence();
              $occurrence->setPlace($place);
              $occurrence->setStartDate(clone $startDay);
              $occurrence->getStartDate()->setTime($startTime->format('H'), $startTime->format('i'));
              $occurrence->setEndDate(clone $startDay);
              $occurrence->getEndDate()->setTime($endTime->format('H'), $endTime->format('i'));
              $occurrences[] = $occurrence;
            }

            $startDay->add(new \DateInterval('P1D'));
          }

          if ($occurrences->count() > 0) {
            $event->getOccurrences()->clear();
            $event->setOccurrences($occurrences);
            $message = $event->getId()
              ? sprintf('Updated %d occurrence(s)', count($occurrences))
              : sprintf('Created %d occurrence(s)', count($occurrences));
            $this->addFlash('info', $message);
          }
        }
      }
    }
  }
}
