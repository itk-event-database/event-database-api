<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Controller;

use AdminBundle\Service\TagManager;
use AppBundle\Entity\UnknownTag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UnknownTag controller.
 *
 * @Route("/v1/admin/unknown_tag")
 * @Security("has_role('ROLE_TAG_EDITOR')")
 */
class UnknownTagController extends Controller
{
    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * Lists all UnknownTag entities.
     *
     * @Route("/", name="admin_unknown_tag", methods={"GET"})
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
     * Creates a new UnknownTag entity.
     *
     * @Route("/", name="admin_unknown_tag_create", methods={"POST"})
     *
     * @Template("AppBundle:UnknownTag:new.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $newTag = new UnknownTag();
        $form = $this->createCreateForm($newTag);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $tags = $this->getTagManager()->loadOrCreateTags([$newTag->getName()]);
            if ($tags) {
                $tag = $tags[0];
                $tag->setTag($newTag->getTag());
                $em = $this->getDoctrine()->getManager();
                $em->persist($tag);
                $em->flush();

                $this->addFlash('success', 'UnknownTag '.$tag->getName().' created');

                return $this->redirectToRoute('admin_unknown_tag_show', ['id' => $tag->getId()]);
            }
        }

        return [
        'tag' => $newTag,
        'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new UnknownTag entity.
     *
     * @Route("/new", name="admin_unknown_tag_new", methods={"GET"})
     *
     * @Template()
     */
    public function newAction()
    {
        $tag = new UnknownTag();
        $form = $this->createCreateForm($tag);

        return [
        'tag' => $tag,
        'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a UnknownTag entity.
     *
     * @Route("/{id}", name="admin_unknown_tag_show", methods={"GET"})
     *
     * @Template()
     *
     * @param \AppBundle\Entity\UnknownTag $tag
     *
     * @return array
     */
    public function showAction(UnknownTag $tag)
    {
        $deleteForm = $this->createDeleteForm($tag);

        return [
        'tag' => $tag,
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing UnknownTag entity.
     *
     * @Route("/{id}/edit", name="admin_unknown_tag_edit", methods={"GET"})
     *
     * @Template()
     *
     * @param \AppBundle\Entity\UnknownTag $tag
     *
     * @return array
     */
    public function editAction(UnknownTag $tag)
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
     * Edits an existing UnknownTag entity.
     *
     * @Route("/{id}", name="admin_unknown_tag_update", methods={"PUT"})
     *
     * @Template("AppBundle:UnknownTag:edit.html.twig")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AppBundle\Entity\UnknownTag              $tag
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, UnknownTag $tag)
    {
        $deleteForm = $this->createDeleteForm($tag);
        $editForm = $this->createEditForm($tag);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'UnknownTag '.$tag->getName().' updated');

            return $this->redirectToRoute('admin_unknown_tag');
        }

        return [
        'tag' => $tag,
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a UnknownTag entity.
     *
     * @Route("/{id}", name="admin_unknown_tag_delete", methods={"DELETE"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AppBundle\Entity\UnknownTag              $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, UnknownTag $tag)
    {
        $form = $this->createDeleteForm($tag);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->getTagManager()->deleteTag($tag)) {
                $this->addFlash('success', 'UnknownTag '.$tag->getName().' deleted');
            } else {
                $this->addFlash('error', 'Error deleting tag '.$tag->getName());
            }
        }

        return $this->redirectToRoute('admin_unknown_tag');
    }

    private function getTagManager()
    {
        if (null === $this->tagManager) {
            $this->tagManager = $this->get('unknown_tag_manager');
        }

        return $this->tagManager;
    }

    /**
     * Creates a form to create a UnknownTag entity.
     *
     * @param UnknownTag $tag
     *                        The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(UnknownTag $tag)
    {
        $form = $this->createForm('AdminBundle\Form\UnknownTagType', $tag, [
        'action' => $this->generateUrl('admin_unknown_tag_create'),
        'method' => 'POST',
        ]);

        return $form;
    }

    /**
     * Creates a form to edit a UnknownTag entity.
     *
     * @param UnknownTag $tag
     *                        The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(UnknownTag $tag)
    {
        $form = $this->createForm('AdminBundle\Form\UnknownTagType', $tag, [
        'action' => $this->generateUrl('admin_unknown_tag_update', ['id' => $tag->getId()]),
        'method' => 'PUT',
        ]);

        return $form;
    }

    /**
     * Creates a form to delete a UnknownTag entity.
     *
     * @param UnknownTag $tag
     *                        The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(UnknownTag $tag)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('admin_unknown_tag_delete', ['id' => $tag->getId()]))
        ->setMethod('DELETE')
        ->getForm();
    }
}
