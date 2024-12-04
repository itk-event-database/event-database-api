<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Action;

use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class ResourceAction
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param \ApiPlatform\Core\Bridge\Symfony\Routing\Router $router
     *                                                                The router
     * @param \Twig_Environment                               $twig
     *                                                                The TWIG environment
     */
    public function __construct(RouterInterface $router, ContainerInterface $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * @Route("/v1/resource/", name="resource")
     *
     * Using annotations is not mandatory, XML and YAML configuration files can be used instead.
     * If you want to decouple your actions from the framework, don't use annotations.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request)
    {
        $content = null;

        $path = $request->get('path');

        try {
            $path = $this->container->get('kernel')->locateResource($path);
            $content = file_get_contents($path);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Path does not exist: '.$path);
        }

        $info = pathinfo($path);

        switch ($info['extension']) {
            case 'js':
            case 'json':
                $json = $this->container->get('serializer')->decode($content, 'json');
                $response = new JsonResponse($json);
                $jsonp = $request->get('jsonp');
                if ($jsonp) {
                    $response->setCallback($jsonp);
                }

                return $response;
        }
    }
}
