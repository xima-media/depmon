<?php

namespace Xima\DepmonBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Repository\ProjectRepository;
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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * DefaultController constructor.
     * @param Cache $cache
     */
    public function __construct(Cache $cache, KernelInterface $kernel)
    {
        $this->cache = $cache;
        $this->kernel = $kernel;

//        $this->projectRepository = $this->getDoctrine()->getRepository(Project::class);
    }

    /**
     * Index action.
     * @return Response
     */
    public function index(): Response
    {
        $projects = [];
        $error = null;

        $projects = $this->getDoctrine()->getRepository(Project::class)->findAll();

//        foreach ($projects as $project) {
//            var_dump(count($project->getDependencies()));
//        }

//        $projectsConfig = $this->getParameter('xima_depmon.projects');
//
//        if (is_array($projectsConfig)) {
//            foreach ($projectsConfig as $project) {
//                $projects[] = $this->cache->get($project['name']);
//            }
//        } else {
//            $error = 1;
//        }
        $metadata = $this->cache->get('metadata');

//        if (empty($projects)) {
//            $error = 2;
//        }

        return $this->render('@XimaDepmon/index.html.twig', [
            'projects' => $projects,
            'metadata' => $metadata,
            'error' => $error
        ]);
    }

    /**
     * Detail action
     * @param string $projectName
     * @return Response
     */
    public function detail($project): Response
    {
        $projects = [];
        $error = null;

        $projectsConfig = $this->getParameter('xima_depmon.projects');
        if (is_array($projectsConfig)) {
            foreach ($projectsConfig as $projectData) {
                if ($project == $projectData['name']) {
                    $projects[] = $this->cache->get($projectData['name']);
                }
            }
        } else {
            $error = 1;
        }
        $metadata = $this->cache->get('metadata');

        if (empty($projects)) {
            $error = 3;
        }

        return $this->render('@XimaDepmon/index.html.twig', [
            'projects' => $projects,
            'metadata' => $metadata,
            'detail' => $project,
            'error' => $error
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
            throw $this->createNotFoundException("No data for project \"$project\" found.");
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

    /**
     * Update action
     * @return JsonResponse
     */
    public function update(KernelInterface $kernel): JsonResponse
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'depmon:aggregate'
        ));

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();
        $httpResponseCode = 200;

        if ($application->areExceptionsCaught()) {
            $httpResponseCode = 500;
        }

        return new JsonResponse($content, $httpResponseCode);
    }
}