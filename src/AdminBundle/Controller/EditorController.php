<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AdminBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Editor/SPA controller.
 *
 * @Route("/editor")
 * @Security("has_role('ROLE_EVENT_EDITOR')")
 */
class EditorController extends Controller {

  /**
   * Lists all Feed entities.
   *
   * @Route("/config.js", name="editor_config_js")
   *
   * @Method("GET")
   *
   * @Template()
   */
  public function configAction(Request $request) {
    $em = $this->getDoctrine()->getManager();

    $tags = $em->getRepository('AppBundle:Tag')->findAll();
    $baseUrl = $this->generateUrl('editor_config_js');

    $params = array(
      'tags' => $tags,
      'baseUrl' => $baseUrl
    );
    $response = $this->render('AdminBundle:Editor:config.js.twig', $params);
    $response->headers->set('Content-Type', 'text/javascript');
    return $response;
  }

}
