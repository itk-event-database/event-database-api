<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/tag/list", name="admin_helper_tag_list", methods={"GET"})
     *
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
