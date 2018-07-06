<?php

namespace Xima\DepmonBundle\Controller;


use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Exception\InvalidParameterException;
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

    /**
     * DefaultController constructor.
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Index action.
     * @return Response
     */
    public function index(): Response
    {
//        $projectsConfig = Yaml::parseFile(__DIR__ . '/../../config/depmon.projects.yaml');
        $projectsConfig = $this->getParameter('xima_depmon.projects');
        $projects = [];
        if (is_array($projectsConfig)) {

            foreach ($projectsConfig as $project) {
                $projects[] = $this->cache->get($project['name']);
            }
        }
        $metadata = $this->cache->get('metadata');


        return $this->render('@XimaDepmon/index.html.twig', [
            'projects' => $projects,
            'metadata' => $metadata
        ]);
    }

    /**
     * State action for checking the summary dependency state of a project and returning a SVG.
     * @param string $project
     * @return Response
     */
    public function state($project): Response
    {

        $data = $this->cache->get($project);
        if ($data == '') {
            throw new InvalidParameterException("No data for project \"$project\" found.");
        }
        $state = $data['meta']['projectState'];

        $filename = $project . ".svg";
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'image/svg+xml');
        $response->headers->set('Content-Disposition',
            'filename="' . basename($filename) . '";');
        $response->sendHeaders();

        readfile(__DIR__ . "/../Resources/public/img/states/$state.svg");

        return $response;
    }
}