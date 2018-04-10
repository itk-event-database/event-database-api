<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Entity\TagManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tag controller.
 *
 * @Route("/admin/tag")
 * @Security("has_role('ROLE_TAG_EDITOR')")
 */
class TagController extends Controller
{
    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * Lists all Tag entities.
     *
     * @Route("/", name="admin_tag")
     *
     * @Method("GET")
     *
     * @Template()
     */
    public function indexAction()
    {
        $tags = $this->getTagManager()->loadTags();

        return [
        'tags' => $tags,
        ];
    }

    /**
     * Creates a new Tag entity.
     *
     * @Route("/", name="admin_tag_create")
     *
     * @Method("POST")
     *
     * @Template("AppBundle:Tag:new.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $tag = new Tag();
        $form = $this->createCreateForm($tag);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $tag = $this->getTagManager()->createTag($tag->getName());
            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            $this->addFlash('success', 'Tag '.$tag->getName().' created');

            return $this->redirectToRoute('admin_tag_show', ['id' => $tag->getId()]);
        }

        return [
        'tag' => $tag,
        'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new Tag entity.
     *
     * @Route("/new", name="admin_tag_new")
     *
     * @Method("GET")
     *
     * @Template()
     */
    public function newAction()
    {
        $tag = new Tag();
        $form = $this->createCreateForm($tag);

        return [
        'tag' => $tag,
        'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Tag entity.
     *
     * @Route("/{id}", name="admin_tag_show")
     *
     * @Method("GET")
     *
     * @Template()
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return array
     */
    public function showAction(Tag $tag)
    {
        $deleteForm = $this->createDeleteForm($tag);

        return [
        'tag' => $tag,
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Tag entity.
     *
     * @Route("/{id}/edit", name="admin_tag_edit")
     *
     * @Method("GET")
     *
     * @Template()
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return array
     */
    public function editAction(Tag $tag)
    {
        $editForm = $this->createEditForm($tag);
        $deleteForm = $this->createDeleteForm($tag);

        return [
        'tag' => $tag,
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Tag entity.
     *
     * @Route("/{id}", name="admin_tag_update")
     *
     * @Method("PUT")
     *
     * @Template("AppBundle:Tag:edit.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AppBundle\Entity\Tag                     $tag
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Tag $tag)
    {
        $deleteForm = $this->createDeleteForm($tag);
        $editForm = $this->createEditForm($tag);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'Tag '.$tag->getName().' updated');

            return $this->redirectToRoute('admin_tag');
        }

        return [
        'tag' => $tag,
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Tag entity.
     *
     * @Route("/{id}", name="admin_tag_delete")
     *
     * @Method("DELETE")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AppBundle\Entity\Tag                     $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Tag $tag)
    {
        $form = $this->createDeleteForm($tag);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->getTagManager()->deleteTag($tag)) {
                $this->addFlash('success', 'Tag '.$tag->getName().' deleted');
            } else {
                $this->addFlash('error', 'Error deleting tag '.$tag->getName());
            }
        }

        return $this->redirectToRoute('admin_tag');
    }

    private function getTagManager()
    {
        if (null === $this->tagManager) {
            $this->tagManager = $this->get('tag_manager');
        }

        return $this->tagManager;
    }

    /**
     * Creates a form to create a Tag entity.
     *
     * @param Tag $tag
     *                 The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Tag $tag)
    {
        $form = $this->createForm('AdminBundle\Form\TagType', $tag, [
        'action' => $this->generateUrl('admin_tag_create'),
        'method' => 'POST',
        ]);

        return $form;
    }

    /**
     * Creates a form to edit a Tag entity.
     *
     * @param Tag $tag
     *                 The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Tag $tag)
    {
        $form = $this->createForm('AdminBundle\Form\TagType', $tag, [
        'action' => $this->generateUrl('admin_tag_update', ['id' => $tag->getId()]),
        'method' => 'PUT',
        ]);

        return $form;
    }

    /**
     * Creates a form to delete a Tag entity.
     *
     * @param Tag $tag
     *                 The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Tag $tag)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('admin_tag_delete', ['id' => $tag->getId()]))
        ->setMethod('DELETE')
        ->getForm();
    }
}
