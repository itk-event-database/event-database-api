<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Controller;

use AdminBundle\Entity\Feed;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Feed controller.
 *
 * @Route("/admin/feed")
 * @Security("has_role('ROLE_FEED_EDITOR')")
 */
class FeedController extends Controller
{
    /**
     * Lists all Feed entities.
     *
     * @Route("/", name="admin_feed", methods={"GET"})
     *
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $feeds = $em->getRepository('AdminBundle:Feed')->findAll();

        return [
        'feeds' => $feeds,
        ];
    }

    /**
     * Creates a new Feed entity.
     *
     * @Route("/", name="admin_feed_create", methods={"POST"})
     *
     * @Template("AdminBundle:Feed:new.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $feed = new Feed();
        $form = $this->createCreateForm($feed);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($feed);
            $em->flush();

            $this->addFlash('success', 'Feed '.$feed->getName().' created');

            return $this->redirectToRoute('admin_feed_show', ['id' => $feed->getId()]);
        }

        return [
        'feed' => $feed,
        'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new Feed entity.
     *
     * @Route("/new", name="admin_feed_new", methods={"GET"})
     *
     * @Template()
     */
    public function newAction()
    {
        $feed = new Feed();
        $form = $this->createCreateForm($feed);

        return [
        'feed' => $feed,
        'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Feed entity.
     *
     * @Route("/{id}", name="admin_feed_show", methods={"GET"})
     *
     * @Template()
     *
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return array
     */
    public function showAction(Feed $feed)
    {
        $deleteForm = $this->createDeleteForm($feed);

        return [
        'feed' => $feed,
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Feed entity.
     *
     * @Route("/{id}/edit", name="admin_feed_edit", methods={"GET"})
     *
     * @Template()
     *
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return array
     */
    public function editAction(Feed $feed)
    {
        $editForm = $this->createEditForm($feed);
        $deleteForm = $this->createDeleteForm($feed);

        return [
        'feed' => $feed,
        'form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Feed entity.
     *
     * @Route("/{id}", name="admin_feed_update", methods={"PUT"})
     *
     * @Template("AdminBundle:Feed:edit.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AdminBundle\Entity\Feed                  $feed
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Feed $feed)
    {
        $deleteForm = $this->createDeleteForm($feed);
        $editForm = $this->createEditForm($feed);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'Feed '.$feed->getName().' updated');

            return $this->redirectToRoute('admin_feed_edit', ['id' => $feed->getId()]);
        }

        return [
        'feed' => $feed,
        'form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Feed entity.
     *
     * @Route("/{id}", name="admin_feed_delete", methods={"DELETE"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AdminBundle\Entity\Feed                  $feed
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Feed $feed)
    {
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
     * @Route("/{id}/preview", name="admin_feed_preview", methods={"GET"})
     *
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return array
     */
    public function previewAction(Feed $feed)
    {
        $previewer = $this->get('feed_previewer');
        $previewer->read($feed);
        $events = $previewer->getEvents();

        return new JsonResponse($events);
    }

    /**
     * Validate feed data.
     *
     * @Route("/{id}/validate", name="admin_feed_validate", methods={"GET"})
     *
     * @Template("AdminBundle:Feed:validate.html.twig")
     *
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return array
     */
    public function validateAction(Feed $feed)
    {
        $validator = $this->get('feed_validator');
        $errors = $validator->validate($feed);
        $events = $validator->getEvents();

        return [
        'feed' => $feed,
        'events' => $events,
        'errors' => $errors,
        ];
    }

    /**
     * @Route("/{id}/easyadmin", name="admin_feed_easyadmin_edit", methods={"GET", "POST"})
     *
     * @Security("has_role('ROLE_FEED_EDITOR')")
     */
    public function easyadminEditAction(Request $request, Feed $feed)
    {
        $action = $request->get('action');
        $redirect = $request->get('redirect');

        $manager = $this->get('feed_manager');

        if ('POST' === $request->getMethod()) {
            switch ($action) {
                case 'enable':
                    if ($manager->enable($feed)) {
                        $translator = $this->container->get('translator');
                        $message = $translator->trans('Feed %feed_name% enabled', ['%feed_name%' => $feed->getName()]);
                        $this->addFlash('info', $message);
                    }

                    break;
                case 'disable':
                    if ($manager->disable($feed)) {
                        $translator = $this->container->get('translator');
                        $message = $translator->trans('Feed %feed_name% disabled', ['%feed_name%' => $feed->getName()]);
                        $this->addFlash('info', $message);
                    }

                    break;
            }
        } elseif ('GET' === $request->getMethod()) {
        }

        if ($redirect) {
            return $this->redirect($redirect);
        }

        return $this->redirectToRoute('easyadmin', [
        'action' => 'edit',
        'id' => $feed->getId(),
        'entity' => 'Feed',
        ]);
    }

    /**
     * Creates a form to create a Feed entity.
     *
     * @param Feed $feed
     *                   The feed
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Feed $feed)
    {
        $form = $this->createForm('AdminBundle\Form\FeedType', $feed, [
        'action' => $this->generateUrl('admin_feed_create'),
        'method' => 'POST',
        ]);

        return $form;
    }

    /**
     * Creates a form to edit a Feed entity.
     *
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @internal param \AdminBundle\Entity\Feed $entity The entity*   The entity
     */
    private function createEditForm(Feed $feed)
    {
        $form = $this->createForm('AdminBundle\Form\FeedType', $feed, [
        'action' => $this->generateUrl('admin_feed_update', ['id' => $feed->getId()]),
        'method' => 'PUT',
        ]);

        return $form;
    }

    /**
     * Creates a form to delete a Feed entity by id.
     *
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @internal param mixed $id The entity id*   The entity id
     */
    private function createDeleteForm(Feed $feed)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('admin_feed_delete', ['id' => $feed->getId()]))
        ->setMethod('DELETE')
        ->getForm();
    }
}
