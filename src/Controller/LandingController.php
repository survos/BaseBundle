<?php

namespace Survos\BaseBundle\Controller;

// these should be in oAuthController, I think.  Or handle by and event?
use App\Entity\ApiToken;
use App\Entity\LoginToken;
use App\Entity\User;
use App\Form\ForgotPasswordFormType;
use App\Repository\UserRepository;


use Doctrine\ORM\EntityManagerInterface;
use Mindscreen\YarnLock\YarnLock;
use Survos\BaseBundle\Form\ChangePasswordFormType;
use Survos\BaseBundle\BaseService;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Texts\SVGText;
use SVG\SVG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Yaml\Yaml;

class LandingController extends AbstractController
{
    private $baseService;
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
//        private  UserProviderInterface $userProvider,
        BaseService $baseService,
                                EntityManagerInterface $entityManager,
                                MailerInterface $mailer,
                                ParameterBagInterface $parameterBag,
    )
    {
        $this->baseService = $baseService;
        $this->entityManager = $entityManager;

        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/landing", name="app_landing")
     * @Route("/", name="app_homepage")
     */
    public function landing(Request $request)
    {
        return $this->render("@SurvosBase/landing.html.twig", [
        ]);
    }

    /**
     * @Route("/heroku", name="app_heroku")
     */
    public function heroku(Request $request)
    {
        $process = new Process(['heroku', 'apps:info', '-j']);
        $process->run();
// executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $appInfo = json_decode($process->getOutput());

        return $this->render("@SurvosBase/heroku.html.twig", [
            'appInfo' => $appInfo
        ]);
    }

    /**
     * @Route("/logo", name="app_logo")
     */
    public function logo(Request $request)
    {
        $image = new SVG(100, 100);
        $doc = $image->getDocument();

// circle with radius 20 and green border, center at (50, 50)
        $circle = $doc->addChild(
            (new SVGRect(0, 0, 100, 50))
                ->setStyle('fill', 'none')
                ->setStyle('stroke', '#0F0')
                ->setStyle('stroke-width', '2px')
        );

        $circle->addChild(
            (new SVGText('/HH/', 25+12, 30))
            ->setStyle('text-size', '12px')
        );

        return new Response($image, 200, ['Content-Type' => 'image/svg+xml']);

// rasterize to a 200x200 image, i.e. the original SVG size scaled by 2.
// the background will be transparent by default.
        /*
        $rasterImage = $image->toRasterImage(200, 200);

        header('Content-Type: image/png');
        imagepng($rasterImage);
        */

    }

    /**
     * @Route("/change-locale/{_locale}", name="app_change_locale")
     */
    public function changeLocale(Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        $referer = $request->headers->get('referer'); // get the referer, it can be empty!
        return $this->redirect($referer ? $referer : $urlGenerator->generate('app_homepage'));
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function profile(Request $request)
    {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }
        return $this->render('@SurvosBase/profile.html.twig', [

            'user' => $this->getUser(),
            'oauthClients' => $this->baseService->getOauthClients(),
            'oauthClientKeys' => $this->baseService->getOauthClientKeys(),
            'change_password_form' => $form->createView()
        ]);

        return $this->render("@SurvosBase/profile.html.twig", [
        ]);
    }

    /**
     * @Route("/typography", name="app_typography")
     */
    public function typography(Request $request)
    {
        return $this->render("@SurvosBase/typography.html.twig", [
            'user' => $this->getUser()
        ]);
    }


//    /**
//     * @Route("/impersonate", name="redirect_to_impersonate")
//     */
//    public function impersonate(Request $request)
//    {
//        $id = $request->get('id');
//        if (!$user = $this->entityManager->find(User::class, $id)) {
//            return new NotFoundHttpException("Hmm, user $id wasn't found!");
//        }
//
//        $redirectUrl =$this->generateUrl('app_homepage', ['_switch_user' => $user->getEmail() ]);
//        return new RedirectResponse($redirectUrl);
//    }
//
    /**
     * @Route("/credits/{type}", name="survos_base_credits")
     */
    public function credits(Request $request, string $type='')
    {
        // bad practice to inject the kernel.  Maybe read composer.json and composer.lock
        $json = file_get_contents('../composer.json');
        $lock = file_get_contents('../composer.lock');
        $yarnLockFile = $this->parameterBag->get('kernel.project_dir') . '/yarn.lock';
        $packageFile = $this->parameterBag->get('kernel.project_dir') . '/package.json';

        $packageData = json_decode(file_get_contents($packageFile));
        $packageDependencies = $packageData->dependencies ?? [];
        $packageDevDependencies = $packageData->devDependencies ?? [];
        // dd($packageDevDependencies);
        $allPackages = array_merge((array)$packageDevDependencies, (array)$packageDependencies);


        /*
        if (file_exists($yarnLockFile)) {
            $yarnLock = YarnLock::fromString($yarnData = file_get_contents($yarnLockFile));
            dd($yarnLock, $yarnData);
            $allPackages = $yarnLock->getPackages();
        } else {
            $allPackages = []; // no yarn packages
        }
        */

        /*
        $hasBabelCore = $yarnLock->hasPackage('babel-core', '^6.0.0');
        $babelCorePackages = $yarnLock->getPackagesByName('babel-core');
        $babelCoreDependencies = $babelCorePackages[0]->getDependencies();
        */
        /*
        $yarnLock = file_get_contents('../yarn.lock');
        $modules = array_map(function($libData) {
            if ($libData) {
                $x = Yaml::parse($libData);
                dd($libData, $x);
            }
        }, explode("\n", $yarnLock));

        $modules = Yaml::parse($yarnLock);
        dd($modules);
        */

        /*
        $json = exec(sprintf('yarn list  --json') );
        $data = json_decode($json, true)['data']['trees'];
        */

        return $this->render("@SurvosBase/credits.html.twig", [
            'type' => $type,
            'composerData' => json_decode($json, true),
            'yarnModules' => $allPackages
        ]);
    }

    /**
     * @Route("/login-with-token", name="app_login_with_token")
     */
    public function loginWithToken(Request $request)
    {

        // the authenticator should catch this
    }

    /**
     * @Route("/change-password", name="app_change_password")
     */
    public function changePassword(Request $request)
    {

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        return $this->render('@SurvosBase/profile.html.twig', [
            'change_password_form' => $form->createView()
        ]);

        // the authenticator should catch this
    }

//    private function getLoginMessage($email, $loginUrl) {
//        $message = (new \Swift_Message('One Time Login'))
//            ->setFrom('tacman@gmail.com')
//            ->setTo($email)
//            ->setBody(
//                $this->renderView(
//                // templates/emails/registration.html.twig
//                    '@SurvosBase/email/forgot.html.twig',
//                    ['email' => $email, 'url' => $loginUrl]
//                ),
//                'text/html'
//            )
//
//            // you can remove the following code if you don't define a text version for your emails
//                /*
//            ->addPart(
//                $this->renderView(
//                // templates/emails/registration.txt.twig
//                    'emails/registration.txt.twig',
//                    ['name' => $name]
//                ),
//                'text/plain'
//            )
//                */
//        ;
//
//        return $message;
//
//    }

    /**
     * @Route("/one-time-login-request", name="app_one_time_login_request")
    public function oneTimeLoginRequest(Request $request)
    {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // first, see if they have one
            $email = $form->get('email')->getData();
            if (!$user = $this->userProvider->loadUserByUsername($email)) {
                $this->addFlash('error', "Email $email not found");
                return $this->redirectToRoute('app_one_time_login_request');
            }

            if (!$oneTimeLogin = $this->entityManager->getRepository(LoginToken::class)->findOneBy(['user' => $user])) {
                $oneTimeLogin = new LoginToken($user);
                $this->entityManager->persist($oneTimeLogin);
                $this->entityManager->flush();
            }
            try {
            } catch (\Exception $e) {
            }

                $loginLink = $this->generateUrl('app_login_with_token', ['_one_time_token' => $oneTimeLogin->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);
                $message = $this->getLoginMessage($email, $loginLink);
                $this->mailer->send($message);
            try {
            } catch (\Exception $exception) {
                // something's wrong with sending the message
            }
            $this->addFlash('notice', "Email sent to $email");

            return $this->redirectToRoute('app_one_time_login_request', [

            ]);
        }

        return $this->render("@SurvosBase/forgot_password.html.twig", [
            'form' => $form->createView()
        ]);


    }
     *
     */


}
