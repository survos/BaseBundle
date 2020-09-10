<?php

namespace Survos\BaseBundle\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\EventDispatcher\Event;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'survos:user:create';
    private $passwordEncoder;
    private $userProvider;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var GuardAuthenticatorHandler
     */
    private $guardHandler;
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder,
                                // GuardAuthenticatorHandler $guardHandler,
                                UserProviderInterface $userProvider,
//                                EventDispatcherInterface $eventDispatcher,
                                EntityManagerInterface $entityManager,  string $name = null)
    {
        parent::__construct($name);
        $this->passwordEncoder = $passwordEncoder;
        $this->userProvider = $userProvider;
        $this->entityManager = $entityManager;
//        $this->guardHandler = $guardHandler;
//        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a user record with email and password')
            ->addArgument('email', InputArgument::REQUIRED, 'email address of account')
            ->addArgument('password', InputArgument::OPTIONAL, 'Plain text password')
            ->addOption('roles', null, InputOption::VALUE_OPTIONAL, 'comma-delimited list of roles')
            ->addOption('password', null, InputOption::VALUE_NONE, 'Update password')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Change password/roles if account exists.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        $password = $input->getOption('password');
        $email = $input->getArgument('email');

        try {
            $user = $this->userProvider->loadUserByUsername($email);
            if (!$password && !$input->getOption('roles') ) {
                $io->error("$email already exists, use --password to overwrite the existing password");
                return 1;
            } else {
                $action = 'updated';
            }
        } catch (UsernameNotFoundException $usernameNotFoundException) {
            $action = 'created';
            $user = new User();
            $user->setEmail($email);
            $this->entityManager->persist($user);
        }

        if ( (!$plainTextPassword = $input->getArgument('password')) || $password) {
            // password prompt
                $question = new Question('Please choose a password:');
                $question->setValidator(function ($password) {
                    if (empty($password)) {
                        throw new \Exception('Password can not be empty');
                    }

                    return $password;
                });
                $question->setHidden(true);
                $plainTextPassword = $this->getHelper('question')->ask($input, $output, $question);
        }

        if ($roleString = $input->getOption('roles')) {
            $user->setRoles(explode(',', $roleString));
        }

        $user
            ->setPassword($this->passwordEncoder->encodePassword($user, $plainTextPassword));

        
        $this->entityManager->flush();

        if ($output->isVerbose()) {
            // could do a cool table here.
            $table = new Table($output);
            $table
                ->setHeaders(['Field', 'Value'])
                ->setRows([
                    ['email', $user->getEmail()],
                    ['roles', join(',', $user->getRoles())],
                ])
            ;
            $table->render();

        }

        $io->success("User $email $action");
        return 0;
    }
}
