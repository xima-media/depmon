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

    /**
     * @return Response
     */
    public function index(): Response
    {
//        $projectsConfig = Yaml::parseFile(__DIR__ . '/../../config/depmon.projects.yaml');
        $projectsConfig = $this->getParameter('xima_depmon.projects');
        $projects = [];


        foreach ($projectsConfig as $project) {
            $projects[] = $this->cache->get($project['name']);
        }

        $metadata = $this->cache->get('metadata');


        return $this->render('@XimaDepmon/index.html.twig', [
            'projects' => $projects,
            'metadata' => $metadata
        ]);
    }

    /**
     * @param string $project
     * @return Response
     */
    public function state($project): Response {

        $data = $this->cache->get($project);
        if ($data == '') {
            throw new InvalidParameterException("No data for project \"$project\" found.");
        }
        $state = $data['meta']['projectState'];
        $file =  readfile(__DIR__ . "/../Resources/public/img/states/$state.svg");
        $headers = array(
            'Content-Type'     => 'image/svg',
            'Content-Disposition' => 'inline; filename="'.$project.'.svg"');
        return new Response($file, 200, $headers);
    }
}