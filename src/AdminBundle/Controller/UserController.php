<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * User controller.
 *
 * @Route("/v1/admin/user")
 * @Security("has_role('ROLE_USER_EDITOR')")
 */
class UserController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/", name="admin_user", methods={"GET"})
     *
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();

        return [
        'users' => $users,
        ];
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/", name="admin_user_create", methods={"POST"})
     *
     * @Template("AdminBundle:User:new.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $form = $this->createCreateForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userManager->updateUser($user);

            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return [
        'user' => $user,
        'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="admin_user_new", methods={"GET"})
     *
     * @Template()
     */
    public function newAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form = $this->createCreateForm($user);

        return [
        'user' => $user,
        'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}", name="admin_user_show", methods={"GET"})
     *
     * @Template()
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return array
     */
    public function showAction(User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return [
        'user' => $user,
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="admin_user_edit", methods={"GET"})
     *
     * @Template()
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return array
     */
    public function editAction(User $user)
    {
        $editForm = $this->createEditForm($user);
        $deleteForm = $this->createDeleteForm($user);

        return [
        'user' => $user,
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="admin_user_update", methods={"PUT"})
     *
     * @Template("AdminBundle:User:edit.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AppBundle\Entity\User                    $user
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, User $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createEditForm($user);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('admin_user');
        }

        return [
        'user' => $user,
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="admin_user_delete", methods={"DELETE"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AppBundle\Entity\User                    $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_user');
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $user
     *                   The user
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $user)
    {
        $form = $this->createForm('AdminBundle\Form\UserType', $user, [
        'action' => $this->generateUrl('admin_user_create'),
        'method' => 'POST',
        ]);

        return $form;
    }

    /**
     * Creates a form to edit a User entity.
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @internal param \AppBundle\Entity\User $entity The entity*   The entity
     */
    private function createEditForm(User $user)
    {
        $form = $this->createForm('AdminBundle\Form\UserType', $user, [
        'action' => $this->generateUrl('admin_user_update', ['id' => $user->getId()]),
        'method' => 'PUT',
        ]);

        return $form;
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @internal param mixed $id The entity id*   The entity id
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('admin_user_delete', ['id' => $user->getId()]))
        ->setMethod('DELETE')
        ->getForm();
    }
}
