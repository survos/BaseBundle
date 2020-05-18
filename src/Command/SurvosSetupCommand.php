<?php

namespace Survos\BaseBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class SurvosSetupCommand extends Command
{
    protected static $defaultName = 'survos:configure';

    private $projectDir;
    private $kernel;
    private $em;
    private $twig;

    /** @var SymfonyStyle */
    private $io;

    CONST recommendedBundles = [
        'EasyAdminBundle' => ['repo' => 'admin'],
        'SurvosWorkflowBundle' => ['repo' => 'survos/workflow-bundle'],
        // 'MsgPhpUserBundle' => ['repo' => 'msgphp/user-bundle']
    ];

    CONST requiredJsLibraries = [
        'jquery',
        'bootstrap',
        'fontawesome',
        '@fortawesome/fontawesome-free',
        'popper.js'
    ];

    public function __construct(KernelInterface $kernel, EntityManagerInterface $em, \Twig\Environment $twig, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->projectDir = $kernel->getProjectDir();
        $this->twig = $twig;
        $this->em = $em;
    }

    public function setEntityManager(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    protected function configure()
    {

        $this
            ->setDescription('Setup libraries and basic base page')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = $io = new SymfonyStyle($input, $output);

        $bundles = $this->checkBundles($io);
        $yarnPackages = $this->checkYarn($io);
        $this->updateAssets($io, ['bundles' => $bundles, 'yarnPackages' => $yarnPackages]);

        // $this->checkEntities($io);
        $this->createConfig($io);

        $io->success('Base Configuration Complete.');
        return 0;
    }

    private function checkYarn(SymfonyStyle $io)
    {
        if (!file_exists($this->projectDir . '/yarn.lock')) {
            $io->error("run yarn install or bin/console survos:prepare first");
            die();
        }

        try {
            $json = exec(sprintf('yarn list --pattern "(%s)" --json', join('|', self::requiredJsLibraries)) );

            $yarnModules = json_decode($json, true);

            $modules = array_map(function ($tree) {
                if (is_string($tree)) {
                    return $tree;
                }
                if ( preg_match('/(.*)(@[\d\.]+)$/', $tree['name'], $m) ) {
                    $name = $m[1];
                }
                // [$name, $version] = explode('@', $tree['name']);
                return $name;
            }, $yarnModules['data']['trees'] );

            // sort($modules); dump($modules); die();
        } catch (\Exception $e) {
            $io->error("Yarn failed -- is it installed? " . $e->getMessage());
        }

        $missing = array_diff(self::requiredJsLibraries, $modules);

        if ($missing) {
            $io->error("Missing " . join(',', $missing));
            $command = sprintf("yarn add %s --dev", join(' ', $missing));
            if ($io->confirm("Install them now? with $command? ", true)) {
                echo exec($command) . "\n";
                return $this->checkYarn($io); // recursive hack, should be refactored!
            } else {
                die("Cannot continue without yarn modules");
            }
        } else {
            return $modules;
        }


        /* better: */
        /*
        $process = new Process(['yarn', 'run', 'encore', 'dev']);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();
        */




    }

    private function createConfig(SymfonyStyle $io) {

        $yaml = <<< END
services:
  survos.base_menu_builder:
    class: Survos\BaseBundle\Menu\BaseMenuBuilder
    arguments:
      - "@knp_menu.factory"
      - "@security.authorization_checker"
      - "@security.token_storage"
      - "@knpu.oauth2.registry"
    tags:
      #      - { name: knp_menu.menu_builder, method: createMainMenu, alias: base_menu } # The alias is what is used to retrieve the menu
      - { name: knp_menu.menu_builder, method: createSocialMenu, alias: social_menu }
      - { name: knp_menu.menu_builder, method: createTestMenu, alias: base_menu }
      - { name: knp_menu.menu_builder, method: createAuthMenu, alias: auth_menu }

  app.menu_builder:
    class: App\Menu\MenuBuilder
    arguments:
      - "@knp_menu.factory"
      - "@security.authorization_checker"
      - "@security.token_storage"
      - "@knpu.oauth2.registry"
    tags:
      - { name: knp_menu.menu_builder, method: createMainMenu, alias: base_menu }
      - { name: knp_menu.menu_builder, method: createMainMenu, alias: base_content_menu }
      
twig:
  globals:
    base_menu_route: base_menu
    base_content_menu_route: base_menu
      
END;

        if ($prefix = $io->ask("Application Menu Class", 'App/Menu/MenuBuilder')) {
            $dir = $this->projectDir . '/src/Menu';
            $fn = '/src/Menu/MenuBuilder.php';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $php = $this->twig->render("@SurvosBase/MenuBuilder.php.twig", []);

            // $yaml =  Yaml::dump($config);
            file_put_contents($output = $this->projectDir . $fn, $php);
            $io->comment($fn . " written.");


            // use twig? Php?
            $fn = '/config/packages/survos_base.yaml';
            file_put_contents($output = $this->projectDir . $fn, $yaml);
            $io->comment($fn . "  written.");
        }
    }

    private function checkEntities(SymfonyStyle $io) {
        $entities = array();
        $em = $this->em;
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entities[] = $m->getName();
        }

        // if there are entities and easyadmin, create the easyadmin.yaml file??
        dump($entities);
    }

    private function updateAssets(SymfonyStyle $io, array $params) {
        $fn = '/templates/base.html.twig';
        if ($io->confirm("Replace app assets (js and css)?")) {
            // @todo: specific to yarn packages
            try {
                $this->writeFile('/assets/js/app.js', $this->twig->render("@SurvosBase/app.js.twig", $params) );
                $this->writeFile('/assets/css/app.css', $this->twig->render("@SurvosBase/app.css.twig", $params) );
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }

        // this might be running with --watch, but this makes sure it happens after the write.
        echo exec('yarn run encore dev');
    }

    private function checkBundles(SymfonyStyle $io): array
    {
        $bundles = $this->kernel->getBundles();

        foreach (self::recommendedBundles as $bundleName=>$info) {
            if (empty($bundles[$bundleName])) {
                $io->warning($bundleName . ' is recommended, install it using composer req ' . $info['repo']);
            }
        }

        foreach ($bundles as $bundleName) {

        }

        return $bundles;

    }

    private function writeFile($fn, $contents) {
        file_put_contents($output = $this->projectDir . $fn, $contents);
        $this->io->success($fn . " written.");
    }
}
