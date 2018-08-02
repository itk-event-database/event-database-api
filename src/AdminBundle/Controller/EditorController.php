<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Controller;

use AdminBundle\Entity\Tag;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Editor/SPA controller.
 *
 * @Route("/editor")
 * @Security("has_role('ROLE_EVENT_EDITOR')")
 */
class EditorController extends Controller
{
    /**
     * Lists all Feed entities.
     *
     * @Route("/config.js", name="editor_config_js", methods={"GET"})
     *
     * @Template()
     */
    public function configAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository('AppBundle:Tag')->findAll();
        $baseUrl = $this->generateUrl('editor_config_js');

        $params = [
        'tags' => $tags,
        'baseUrl' => $baseUrl,
        ];
        $response = $this->render('AdminBundle:Editor:config.js.twig', $params);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }
}
