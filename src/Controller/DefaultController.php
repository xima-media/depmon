<?php

namespace Xima\DepmonBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Xima\DepmonBundle\Entity\Dependency;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Entity\Form\Filter;
use Xima\DepmonBundle\Form\FilterType;
use Xima\DepmonBundle\Repository\ProjectRepository;
use Xima\DepmonBundle\Service\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Xima\DepmonBundle\Util\Helper;
use Xima\DepmonBundle\Util\VersionHelper;

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
     * @param Filter|null $demand
     * @return Response
     */
    public function index(Request $request): Response
    {
        $error = null;

        $projects = $this->getDoctrine()->getRepository(Project::class)->findAll();

        $filter = new Filter();
        $form = $this->createForm(FilterType::class, $filter, [
            'action' => $this->generateUrl('xima_depmon_ajax'),
            'method' => 'POST',
        ]);

        // Actually this is not working
        // $form->handleRequest($request);
        $form->submit($request->get($form->getName()));


        if ($form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $filter = $form->getData();

            $dependencies = $this->getDoctrine()->getRepository(Dependency::class)->findAll($filter);

            $projects = Helper::groupDependencies($dependencies);
        }

        $metadata = $this->cache->get('metadata');


        return $this->render('@XimaDepmon/index.html.twig', [
            'projects' => $projects,
            'form' => $form->createView(),
            'filter' => new Filter(),
            'metadata' => $metadata,
            'error' => $error,
            'searchWord' => $filter->getDependencySearch() ?: ''
        ]);
    }

    /**
     * Ajax action
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function ajax(Request $request): Response {
        $filter = new Filter();
        $form = $this->createForm(FilterType::class, $filter, [
            'method' => 'POST',
        ]);

        $form->submit($request->get($form->getName()));


        if ($form->isValid()) {

            $filter = $form->getData();

            $dependencies = $this->getDoctrine()->getRepository(Dependency::class)->findAll($filter);

            $projects = Helper::groupDependencies($dependencies);

        }
        return $this->render('@XimaDepmon/list.html.twig', [
            'projects' => $projects,
            'searchWord' => $filter->getDependencySearch() ?: ''
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