<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\TagManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\UnknownTag;

/**
 * UnknownTag controller.
 *
 * @Route("/admin/unknown_tag")
 */
class UnknownTagController extends Controller {
  /**
   * @var TagManager
   */
  private $tagManager;

  private function getTagManager() {
    if ($this->tagManager === null) {
      $this->tagManager = $this->get('unknown_tag_manager');
    }

    return $this->tagManager;
  }

    /**
     * Lists all UnknownTag entities.
     *
     * @Route("/", name="admin_unknown_tag")
     * @Method("GET")
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
     * @Route("/", name="admin_unknown_tag_create")
     * @Method("POST")
     * @Template("AppBundle:UnknownTag:new.html.twig")
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

                $this->addFlash('success', 'UnknownTag ' . $tag->getName() . ' created');
                return $this->redirect($this->generateUrl('admin_tag_show', ['id' => $tag->getId()]));
            }
        }

        return [
            'tag' => $tag,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a form to create a UnknownTag entity.
     *
     * @param UnknownTag $tag The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(UnknownTag $tag)
    {
        $form = $this->createForm('AdminBundle\Form\UnknownTagType', $tag, [
            'action' => $this->generateUrl('admin_tag_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Displays a form to create a new UnknownTag entity.
     *
     * @Route("/new", name="admin_unknown_tag_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $tag = new UnknownTag();
        $form   = $this->createCreateForm($tag);

        return [
            'tag' => $tag,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Finds and displays a UnknownTag entity.
     *
     * @Route("/{id}", name="admin_unknown_tag_show")
     * @Method("GET")
     * @Template()
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
     * @Route("/{id}/edit", name="admin_unknown_tag_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction(UnknownTag $tag)
    {
        $editForm = $this->createEditForm($tag);
        $deleteForm = $this->createDeleteForm($tag);

        return [
            'tag'      => $tag,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
    * Creates a form to edit a UnknownTag entity.
    *
    * @param UnknownTag $tag The tag
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(UnknownTag $tag)
    {
        $form = $this->createForm('AdminBundle\Form\UnknownTagType', $tag, [
            'action' => $this->generateUrl('admin_tag_update', ['id' => $tag->getId()]),
            'method' => 'PUT',
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }
    /**
     * Edits an existing UnknownTag entity.
     *
     * @Route("/{id}", name="admin_unknown_tag_update")
     * @Method("PUT")
     * @Template("AppBundle:UnknownTag:edit.html.twig")
     */
    public function updateAction(Request $request, UnknownTag $tag)
    {
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($tag);
        $editForm = $this->createEditForm($tag);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->addFlash('success', 'UnknownTag ' . $tag->getName() . ' updated');
            $em->flush();

            return $this->redirect($this->generateUrl('admin_tag_edit', ['id' => $tag->getId()]));
        }

        return [
            'tag'      => $tag,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }
    /**
     * Deletes a UnknownTag entity.
     *
     * @Route("/{id}", name="admin_unknown_tag_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, UnknownTag $tag)
    {
        $form = $this->createDeleteForm($tag);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->getTagManager()->deleteTag($tag)) {
              $this->addFlash('success', 'UnknownTag ' . $tag->getName() . ' deleted');
            } else {
              $this->addFlash('error', 'Error deleting tag ' . $tag->getName());
            }
        }

        return $this->redirect($this->generateUrl('admin_tag'));
    }

    /**
     * Creates a form to delete a UnknownTag entity.
     *
     * @param UnknownTag $tag The tag
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(UnknownTag $tag) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_tag_delete', ['id' => $tag->getId()]))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }
}
