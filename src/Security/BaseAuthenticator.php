<?php

namespace Survos\BaseBundle\Security;

use App\Entity\User; // your user entity, should be passed in as a userClass
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BaseAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager,
                                RouterInterface $router,
    private UserProviderInterface $userProvider
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'oauth_connect_check';
    }

    public function authenticate(Request $request): Passport
    {
        $clientKey = $request->get('clientKey');
        $client = $this->clientRegistry->getClient($clientKey);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                $userFromToken = $client->fetchUserFromToken($accessToken);

                $email = $userFromToken->getEmail();

                $existingUser = $this->userProvider->loadUserByIdentifier($email);
//                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['facebookId' => $userFromToken->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

//                // 2) do we have a matching user by email?
//                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                // if we're using email as the identifier, we don't really need the token.  Hmm?

                // 3) Maybe you just want to "register" them by creating
                // a User object
//                $user->setFacebookId($userFromToken->getId());
//                $this->entityManager->persist($user);
//                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('app_homepage');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
