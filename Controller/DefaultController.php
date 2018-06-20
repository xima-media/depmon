<?php

namespace Xima\DepmonBundle\Controller;


use Xima\DepmonBundle\Service\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DefaultController
 * @package Xima\DepmonBundle\Controller
 */
class DefaultController extends AbstractController
{

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function index(): Response
    {
//        $projectsConfig = Yaml::parseFile(__DIR__ . '/../../config/depmon.projects.yaml');
        $projectsConfig = $this->container->getParameter('xima_depmon.projects');
        $projects = [];


        foreach ($projectsConfig as $project) {
            $projects[] = $this->cache->get($project['name']);
        }

        $metadata = $this->cache->get('metadata');


        return $this->render('default/index.html.twig', [
            'projects' => $projects,
            'metadata' => $metadata
        ]);
    }
}