<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\TagManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Tag;

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
   * @Template()
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
