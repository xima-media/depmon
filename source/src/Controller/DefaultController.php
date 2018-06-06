<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends AbstractController
{

    public function index(AdapterInterface $cache): Response
    {
        $cache = new FilesystemCache(
            'depmon',
            3600,
            __DIR__ . '/../../var/cache/test'
        );

        $projectsConfig = Yaml::parseFile(__DIR__ . '/../../config/depmon.yaml');
        $projects = [];

        foreach ($projectsConfig['projects'] as $project) {
            if ($cache->has($project['name'])) {
                $projects[] = $cache->get($project['name']);
            }
        }


        return $this->render('default/index.html.twig', ['projects' => $projects]);
    }
}