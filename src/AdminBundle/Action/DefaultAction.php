<?php

namespace AdminBundle\Action;

use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultAction {
    private $router;
    private $twig;

    public function __construct(Router $router, \Twig_Environment $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @Route("/", name="default")
     *
     * Using annotations is not mandatory, XML and YAML configuration files can be used instead.
     * If you want to decouple your actions from the framework, don't use annotations.
     */
    public function __invoke(Request $request)
    {
        return new Response($this->twig->render('AdminBundle:Default:index.html.twig'));
    }
}
