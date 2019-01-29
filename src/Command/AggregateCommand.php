<?php

namespace Xima\DepmonBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xima\DepmonBundle\Entity\Dependency;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Service\Aggregator;
use Xima\DepmonBundle\Service\Cache;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xima\DepmonBundle\Util\Helper;

/**
 * Class AggregateCommand
 * @package Xima\DepmonBundle\Command
 */
class AggregateCommand extends ContainerAwareCommand
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('depmon:aggregate')

            // the short description shown while running "php bin/console list"
            ->setDescription('Aggregate command.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command is fetching all composer information of every project.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $entityManager EntityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getEntityManager();

        $io = new SymfonyStyle($input, $output);

        $io->text("<fg=red>██████╗ ███████╗██████╗ ███╗   ███╗ ██████╗ ███╗   ██╗</>");
        $io->text("<fg=red>██╔══██╗██╔════╝██╔══██╗████╗ ████║██╔═══██╗████╗  ██║</>");
        $io->text("<fg=red>██║  ██║█████╗  ██████╔╝██╔████╔██║██║   ██║██╔██╗ ██║</>");
        $io->text("<fg=red>██║  ██║██╔══╝  ██╔═══╝ ██║╚██╔╝██║██║   ██║██║╚██╗██║</>");
        $io->text("<fg=red>██████╔╝███████╗██║     ██║ ╚═╝ ██║╚██████╔╝██║ ╚████║</>");
        $io->text("<fg=red>╚═════╝ ╚══════╝╚═╝     ╚═╝     ╚═╝ ╚═════╝ ╚═╝  ╚═══╝</>");

        $io->title("Aggregate project data");

//        $projects = Yaml::parseFile('config/depmon.projects.yaml');
        $projects = $this->getContainer()->getParameter('xima_depmon.projects');

        $progressBar = new ProgressBar($output, count($projects));
        $progressBar->setFormat('verbose');

        $projectDataCount = count($projects);
        $io->text("Fetching data for $projectDataCount projects ...");


        Aggregator::checkIfGitIsInstalled();
        Aggregator::checkIfComposerIsInstalled();


        $dependencyCount = 0;

        foreach ($projects as $projectData) {
            try {

                $projectDataRepository = $this->getContainer()->get('doctrine')->getRepository(Project::class);
                /* @var $projectDataRepository \Xima\DepmonBundle\Repository\ProjectRepository */

                $projectDataName = "<fg=blue;options=bold>[" . $projectData['name'] . "]</>";
                $output->write($projectDataName . " Initializing project ... ");

                // Check if project already exists
                $project = $projectDataRepository->findBy(['name' => $projectData['name']]);
                if (count($project) != 0) {
                    $project = $project[0];
                } else {
                    $project = new Project();
                }
                $project->setName($projectData['name']);
                $project->setGit($projectData['git']);
                $project->setPath($projectData['path']);
                $output->writeln(" <fg=green>✔</>");


                $output->write($projectDataName . " Update project data ... ");
                Aggregator::updateProjectData($project);
                $output->writeln(" <fg=green>✔</>");

                $output->write($projectDataName . " Check composer files ... ");
                Aggregator::checkIfComposerJsonExists($project);

                $composerLock = Aggregator::checkIfComposerLockExists($project);
                if (!Aggregator::validateComposerFiles($project)) {
                    $output->writeln(" <fg=yellow>Composer files are not valid</>");
                } else {
                    $output->writeln(" <fg=green>✔</>");
                };

                $output->write($projectDataName . " Install composer dependencies ... ");
                Aggregator::installComposerDependencies($project, $composerLock);
                $output->writeln(" <fg=green>✔</>");

                $output->write($projectDataName . " Last modification date ... ");
                $date = Aggregator::getLastModificationDate($project);
                $project->setUpdated($date);
                $output->writeln("<fg=green>" . $date->format("d.m.Y, H:m") . "</>");


                $output->write($projectDataName . " Fetch git tag ... ");
                $gitTag = Aggregator::fetchGitTag($project);
                if ($gitTag != '') {
                    $output->writeln(" <fg=green>$gitTag</>");
                } else {
                    $output->writeln(" <fg=yellow>?</>");
                }
                $project->setVersion($gitTag);

                $output->write($projectDataName . " Fetch composer data ... ");
                $data = Aggregator::fetchComposerData($project);
                $output->writeln(" <fg=green>✔</>");

                $output->write($projectDataName . " Check vulnerabilities ... ");
                $vulnerabilities = Aggregator::checkVulnerabilities($project);
                if (empty($vulnerabilities)) {
                    $output->writeln(" <fg=green>✔</>");
                } else {
                    $output->writeln(" <fg=red>✘</>");
                }

                // Saving composer information about project to result
                $project->setComposer(Aggregator::getLocalComposerJson($project));

                $output->write($projectDataName . " Process dependencies ");
                foreach ($data->installed as $dependencyData) {
                    $dependency = Helper::processDependency($dependencyData, $project);
                    $project->addDependency($dependency);
                    $output->write(".");
                }
                $output->writeln(" <fg=green>✔</>");

                $output->write($projectDataName . " Build up metadata ... ");
//                $project = Helper::buildUpMetadata($project, $data, $vulnerabilities);
                $output->writeln(" <fg=green>✔</>");

//                if (!empty($data)) {
//                    $count = count($data['dependencies']);
//                    $requiredCount = $data['meta']['requiredPackagesCount'];
//                    $requiredStatesCount = $data['meta']['requiredStatesCount'];
//                    $dependencyCount += $count;
//
//                    $this->cache->set($projectData['name'], $data);
//
//                    switch ($data['meta']['projectState']) {
//                        case 1:
//                            $projectDataState = "<fg=green>up to date</>";
//                            break;
//                        case 2:
//                            $projectDataState = "<fg=yellow>up to date</>";
//                            break;
//                        case 3:
//                        default:
//                            $projectDataState = "<fg=red>out of date</>";
//                            break;
//                        case 4:
//                            $projectDataState = "<fg=black>insecure</>";
//                            break;
//                    }
//
//                    $output->writeln($projectDataName . " $count dependencies, $requiredCount required (<fg=green>$requiredStatesCount[1]</>, <fg=yellow>$requiredStatesCount[2]</>, <fg=red>$requiredStatesCount[3]</>, <fg=black>$requiredStatesCount[4]</>) ... " . $projectDataState);
//
//                    $io->newLine();
//                    $progressBar->advance();
//                    $io->newLine();
//                    $io->newLine();
//
//                }

                $entityManager->persist($project);
                $entityManager->flush();

            } catch (InvalidArgumentException $e) {

            }

//            Aggregator::clearProjectData($projectData);
        }


        $metadata = [
            'time' => time(),
            'dependencyCount' => $dependencyCount
        ];

//        $this->cache->set('metadata', $metadata);
    }
}