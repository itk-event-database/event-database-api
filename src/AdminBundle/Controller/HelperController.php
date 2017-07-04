<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Helper controller.
 *
 * @Route("/helper")
 */
class HelperController extends Controller
{
    /**
     * Lists all Tag entities.
     *
     * @Route("/tag/list", name="admin_helper_tag_list")
     *
     * @Method("GET")
     * @Template("AdminBundle:Helper:tagList.html.twig")
     */
    public function tagListAction()
    {
        $tagManager = $this->get('tag_manager');
        $tags = $tagManager->loadTags();

        return [
            'tags' => $tags,
        ];
    }
}
