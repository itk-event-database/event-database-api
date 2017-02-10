<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Event;
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
}
