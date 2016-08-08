<?php

namespace AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AdminBundle\Entity\Feed;
use AdminBundle\Form\FeedType;

/**
 * Feed controller.
 *
 * @Route("/admin/feed")
 */
class FeedController extends Controller
{

    /**
     * Lists all Feed entities.
     *
     * @Route("/", name="admin_feed")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AdminBundle:Feed')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Feed entity.
     *
     * @Route("/", name="admin_feed_create")
     * @Method("POST")
     * @Template("AdminBundle:Feed:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Feed();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_feed_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Feed entity.
     *
     * @param Feed $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Feed $entity)
    {
        $form = $this->createForm('AdminBundle\Form\FeedType', $entity, array(
            'action' => $this->generateUrl('admin_feed_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Feed entity.
     *
     * @Route("/new", name="admin_feed_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Feed();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Feed entity.
     *
     * @Route("/{id}", name="admin_feed_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminBundle:Feed')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Feed entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Feed entity.
     *
     * @Route("/{id}/edit", name="admin_feed_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminBundle:Feed')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Feed entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Feed entity.
    *
    * @param Feed $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Feed $entity)
    {
        $form = $this->createForm('AdminBundle\Form\FeedType', $entity, array(
            'action' => $this->generateUrl('admin_feed_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Feed entity.
     *
     * @Route("/{id}", name="admin_feed_update")
     * @Method("PUT")
     * @Template("AdminBundle:Feed:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminBundle:Feed')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Feed entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_feed_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Feed entity.
     *
     * @Route("/{id}", name="admin_feed_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdminBundle:Feed')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Feed entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_feed'));
    }

    /**
     * Creates a form to delete a Feed entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_feed_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
