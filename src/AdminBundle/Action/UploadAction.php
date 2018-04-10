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
use League\Uri\Modifiers\Resolve;
use League\Uri\Schemes\Http as HttpUri;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class UploadAction
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
     * @var Resolve
     */
    private $baseUrlResolver;

    /**
     * @var string
     */
    private $uploadsUrl;

    /**
     * Constructor.
     *
     * @param \ApiPlatform\Core\Bridge\Symfony\Routing\Router           $router
     *                                                                             The router
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *                                                                             The container
     */
    public function __construct(RouterInterface $router, ContainerInterface $container)
    {
        $this->router = $router;
        $this->container = $container;

        $this->baseUrlResolver = new Resolve(HttpUri::createFromString($this->container->getParameter('admin.base_url')));
        $this->uploadsUrl = rtrim($this->container->getParameter('admin.uploads_url'), '/');
    }

    /**
     * @Route("/api/upload", name="api_upload", methods={"POST"})
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
        $data = [];

        foreach ($request->files as $file) {
            $path = $this->container->getParameter('admin.uploads_path');
            $filename = uniqid($file->getBaseName()).'.'.$file->guessExtension();
            $file->move($path, $filename);
            $data['file_url'] = $this->baseUrlResolver->__invoke(HttpUri::createFromString($this->uploadsUrl.'/'.$filename))->__toString();
        }

        return new JsonResponse($data);
    }
}
