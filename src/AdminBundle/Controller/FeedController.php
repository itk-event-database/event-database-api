<?php

namespace AdminBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AdminBundle\Entity\Feed;

/**
 * Feed controller.
 *
 * @Route("/admin/feed")
 */
class FeedController extends Controller {

  /**
   * Lists all Feed entities.
   *
   * @Route("/", name="admin_feed")
   *
   * @Method("GET")
   *
   * @Template()
   */
  public function indexAction() {
    $em = $this->getDoctrine()->getManager();

    $feeds = $em->getRepository('AdminBundle:Feed')->findAll();

    return [
      'feeds' => $feeds,
    ];
  }

  /**
   * Creates a new Feed entity.
   *
   * @Route("/", name="admin_feed_create")
   *
   * @Method("POST")
   *
   * @Template("AdminBundle:Feed:new.html.twig")
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function createAction(Request $request) {
    $feed = new Feed();
    $form = $this->createCreateForm($feed);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($feed);
      $em->flush();

      $this->addFlash('success', 'Feed ' . $feed->getName() . ' created');

      return $this->redirectToRoute('admin_feed_show', ['id' => $feed->getId()]);
    }

    return [
      'feed' => $feed,
      'form'   => $form->createView(),
    ];
  }

  /**
   * Creates a form to create a Feed entity.
   *
   * @param Feed $feed
   *   The feed
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createCreateForm(Feed $feed) {
    $form = $this->createForm('AdminBundle\Form\FeedType', $feed, [
      'action' => $this->generateUrl('admin_feed_create'),
      'method' => 'POST',
    ]);

    return $form;
  }

  /**
   * Displays a form to create a new Feed entity.
   *
   * @Route("/new", name="admin_feed_new")
   *
   * @Method("GET")
   *
   * @Template()
   */
  public function newAction() {
    $feed = new Feed();
    $form   = $this->createCreateForm($feed);

    return [
      'feed' => $feed,
      'form'   => $form->createView(),
    ];
  }

  /**
   * Finds and displays a Feed entity.
   *
   * @Route("/{id}", name="admin_feed_show")
   *
   * @Method("GET")
   *
   * @Template()
   * @param \AdminBundle\Entity\Feed $feed
   * @return array
   */
  public function showAction(Feed $feed) {
    $deleteForm = $this->createDeleteForm($feed);

    return [
      'feed'      => $feed,
      'delete_form' => $deleteForm->createView(),
    ];
  }

  /**
   * Displays a form to edit an existing Feed entity.
   *
   * @Route("/{id}/edit", name="admin_feed_edit")
   *
   * @Method("GET")
   *
   * @Template()
   * @param \AdminBundle\Entity\Feed $feed
   * @return array
   */
  public function editAction(Feed $feed) {
    $editForm = $this->createEditForm($feed);
    $deleteForm = $this->createDeleteForm($feed);

    return [
      'feed'      => $feed,
      'form'   => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    ];
  }

  /**
   * Creates a form to edit a Feed entity.
   *
   * @param \AdminBundle\Entity\Feed $feed
   * @return \Symfony\Component\Form\Form The form
   * @internal param \AdminBundle\Entity\Feed $entity The entity*   The entity
   *
   */
  private function createEditForm(Feed $feed) {
    $form = $this->createForm('AdminBundle\Form\FeedType', $feed, [
      'action' => $this->generateUrl('admin_feed_update', ['id' => $feed->getId()]),
      'method' => 'PUT',
    ]);

    return $form;
  }

  /**
   * Edits an existing Feed entity.
   *
   * @Route("/{id}", name="admin_feed_update")
   *
   * @Method("PUT")
   *
   * @Template("AdminBundle:Feed:edit.html.twig")
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \AdminBundle\Entity\Feed $feed
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function updateAction(Request $request, Feed $feed) {
    $deleteForm = $this->createDeleteForm($feed);
    $editForm = $this->createEditForm($feed);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->flush();

      $this->addFlash('success', 'Feed ' . $feed->getName() . ' updated');

      return $this->redirectToRoute('admin_feed_edit', ['id' => $feed->getId()]);
    }

    return [
      'feed'      => $feed,
      'form'   => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    ];
  }

  /**
   * Deletes a Feed entity.
   *
   * @Route("/{id}", name="admin_feed_delete")
   *
   * @Method("DELETE")
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \AdminBundle\Entity\Feed $feed
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function deleteAction(Request $request, Feed $feed) {
    $form = $this->createDeleteForm($feed);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->remove($feed);
      $em->flush();
    }

    return $this->redirectToRoute('admin_feed');
  }

  /**
   * Creates a form to delete a Feed entity by id.
   *
   * @param \AdminBundle\Entity\Feed $feed
   * @return \Symfony\Component\Form\Form The form
   * @internal param mixed $id The entity id*   The entity id
   *
   */
  private function createDeleteForm(Feed $feed) {
    return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_feed_delete', ['id' => $feed->getId()]))
            ->setMethod('DELETE')
            ->getForm();
  }

}
