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
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Yaml\Yaml;

class SurvosInitCommand extends Command
{
    protected static $defaultName = 'survos:init';

    private $projectDir;
    private $kernel;
    private $em;
    private $twig;
    private $toolsRequested; // array of requested tools

    private $appCode;

    /** @var SymfonyStyle */
    private $io;

    CONST recommendedBundles = [
        'EasyAdminBundle',
        'SurvosWorkflowBundle',
        'UserBundle'
    ];

    CONST tools = [
        'heroku',
        'easyadmin',
        'all'
    ];

    CONST requiredJsLibraries = [
        'jquery',
        'bootstrap',
        'fontawesome',
        'popper.js',
        'sass-loader',
        'node-sass'
    ];

    public function __construct(KernelInterface $kernel, EntityManagerInterface $em, \Twig\Environment $twig, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->em = $em;
        $this->projectDir = $kernel->getProjectDir();
        $this->twig = $twig;
    }
    protected function configure()
    {

        $this
            ->setDescription('Basic environment: base page, heroku, yarn install, sqlite in .env.local, ')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('heroku', null, InputOption::VALUE_OPTIONAL, 'heroku name')
        ;
    }

    private function setAppCode($appCode) {
        $this->appCode = $appCode;
    }

    private function getAppCode() {
        // default  is the directory
        if (empty($this->appCode)) {
            $this->appCode = basename($this->kernel->getProjectDir());
        }
       return $this->appCode;
    }


    private function toolRequested($tool)
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = $io = new SymfonyStyle($input, $output);
        $all = in_array('all', $input->getOption('tools'));


        if ($input->getOption('heroku')) {
            $this->checkHeroku($io);
        }

        $this->createFavicon($io);

        // $this->handleFavicon($io);
        // https://favicon.io/favicon-generator/?t=Fa&ff=Lancelot&fs=99&fc=%23FFFFFF&b=rounded&bc=%23B4B

        $this->createTranslations($io);
        $this->checkYarn($io);
        $this->setupDatabase($io);
        $this->updateBase($io);

        // perhaps install required yarn modules here?  Then in setup have the optional ones?

        /*
         *
         */

        // configure the route
        if ($prefix = $io->ask("Base Route Prefix", '/')) {
$routes_by_name_config = <<< END

survos_landing: {path: /landing, controller: 'Survos\BaseBundle\Controller\LandingController::landing'}
app_homepage: {path: /, controller: 'Survos\BaseBundle\Controller\LandingController::landing'}
app_logo: {path: /logo, controller: 'Survos\BaseBundle\Controller\LandingController::logo'}
app_profile: {path: /profile, controller: 'Survos\BaseBundle\Controller\LandingController::profile'}
profile: {path: /profile, controller: 'Survos\BaseBundle\Controller\LandingController::profile'}
logout: {path: /profile, controller: 'Survos\BaseBundle\Controller\LandingController::logout'}
# required if app_profile is used, since you can change the password from the profile
app_change_password: {path: /change-password, controller: 'Survos\BaseBundle\Controller\LandingController::changePassword'}
app_typography: {path: /typography, controller: 'Survos\BaseBundle\Controller\LandingController::typography'}
survos_base_credits: {path: /credits, controller: 'Survos\BaseBundle\Controller\LandingController::credits'}
END;
            $fn = '/config/routes/survos_base.yaml';
            $config = [
//                'survos_base_bundle' => [
//                    'resource' => '@SurvosBaseBundle/Controller/LandingController.php',
//                    'prefix' => $prefix,
//                    'type' => 'annotation'
//                ],
                'survos_base_bundle_oauth' => [
                    'resource' => '@SurvosBaseBundle/Controller/OAuthController.php',
                    'prefix' => $prefix,
                    'type' => 'annotation'
                ],

            ];
            file_put_contents($output = $this->projectDir . $fn, Yaml::dump($config) . "\n\n" . $routes_by_name_config);
            $io->comment($fn . " written.");
        }

        $io->success("Run xterm -e \"yarn run encore dev-server\" & install more bundles, then run bin/console survos:setup");
        return 0;
    }

    private function updateBase(SymfonyStyle $io) {
        $fn = '/templates/base.html.twig';
        if ($io->confirm("Replace $fn?")) {
            $this->writeFile($fn, '{% extends "@SurvosBase/base.html.twig" %}');
        }
    }

    private function createFavicon(SymfonyStyle $io)
    {
        $host = 'https://favicon.io/favicon-generator/?';
        $params = [
            't' => $this->getAppCode()
        ];
        $url = $host . http_build_query($params);
        $io->writeln("Download zip file at $url");


        $fn = $io->ask("zip file name?  Use ~ to skip", './favicon_io.zip');
        if ($fn === '~') {
            return;
        }

        if (!file_exists($fn)) {
            // re-ask
        }
        $zip = new \ZipArchive();
        if ($zip->open($fn) === TRUE) {
            $publicDir = $this->projectDir . '/./public';
            for($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileinfo = pathinfo($filename);
                $io->writeln('Extracting ' . $filename . ' to ' . $publicDir);
                if (!$zip->extractTo($publicDir, array($zip->getNameIndex($i)))) {
                    $io->error(sprintf("Unable to extract %s to %s", $filename, $publicDir));
                }
                // copy("zip://".$path."#".$filename, "/your/new/destination/".$fileinfo['basename']);
            }

            // $zip->extractTo($publicDir);
            $zip->close();
            $io->success('Favicons extracted');
        } else {
            $io->error('Error extracting Favicons');
            return -1;
        }
    }

    private function createTranslations(SymfonyStyle $io) {
        $fn = '/translations/messages.en.yaml'; // @todo: get current default language code
        if ($io->confirm("Replace $fn?")) {
            $appCode = $this->getAppCode();
            $appCode =  $io->ask("Short Code?", $appCode);
            $t = [
                'home' => [
                    'title' => $title = $io->ask('Title?', "$appCode Title"),
                    'intro' => "Intro to $title",
                    'description' => $io->ask('description?', "$appCode *Description*, in _markdown_")
                ]
            ];
            $this->writeFile($fn, Yaml::dump($t, 5));
        }
    }


    private function checkYarn(SymfonyStyle $io)
    {
        if (!file_exists($this->projectDir . '/yarn.lock')) {
            $io->warning("Installing base yarn libraries");
            echo exec('yarn install');
        }
    }

    private function checkHeroku(SymfonyStyle $io)
    {

        $this->io->write("Checking Heroku");
        // @todo: check buildpacks
        $this->io->writeln(exec("heroku buildpacks:add heroku/php"));
        $this->io->writeln(exec("heroku buildpacks:add heroku/nodejs"));

        // @todo: heroku config:set APP_ENV=prod, etc.

        if (!file_exists($this->projectDir . ($fn = '/Procfile'))) {
           //  $io->warning("Installing base yarn libraries");
            $procfile = $this->twig->render("@SurvosBase/heroku/Procfile.twig", []);
            $this->writeFile($fn, $procfile);
        }
        if (!file_exists($this->projectDir . ($fn = '/fpm_custom.conf'))) {
            //  $io->warning("Installing base yarn libraries");
            $procfile = $this->twig->render("@SurvosBase/heroku/fpm_custom.conf.twig", []);
            $this->writeFile($fn, $procfile);
        }

        if (!file_exists($this->projectDir . ($fn = '/heroku-nginx.conf'))) {
            //  $io->warning("Installing base yarn libraries");
            $procfile = $this->twig->render("@SurvosBase/heroku/heroku-nginx.conf.twig", []);
            $this->writeFile($fn, $procfile);
        }

        // fix monolog key
        $monologFile = $this->projectDir . ($fn = '/config/packages/prod/monolog.yaml');
        $data = Yaml::parse(file_get_contents($monologFile));
        $data['monolog']['handlers']['nested']['path'] = "php://stderr";
        $newData = Yaml::dump($data, 4);
        $this->writeFile($fn, $newData);


    }

    private function setupDatabase(SymfonyStyle $io) {
        $localExists = file_exists($fn = $this->projectDir . '/.env.local');
        $data = "MAILER_DSN=smtp://localhost\n\n"; // hack, why doesn't symfony do this??  Maybe do it in the survos recipe?
        if (!$localExists && $io->confirm('Use sqlite database in .env.local', true)) {
            $data .= "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db";
            if (!file_exists($fn = $this->projectDir . '/.env.local')) {
                file_put_contents($fn, $data);
            }
        }
    }

    private function writeFile($fn, $contents) {
        file_put_contents($output = $this->projectDir . $fn, $contents);
        $this->io->success($fn . " written.");
    }

}
