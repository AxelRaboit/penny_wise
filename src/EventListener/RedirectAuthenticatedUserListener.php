<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
final readonly class RedirectAuthenticatedUserListener
{
    private const LOGIN_ROUTE = 'app_login';
    private const HOMEPAGE_ROUTE = 'app_homepage';
    private const REGISTER_ROUTE = 'app_register';

    private const ROUTE = '_route';

    public function __construct(private Security $security, private RouterInterface $router){}


    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $user = $this->security->getUser();
        /** @var string|null $route */
        $route = $request->attributes->get(self::ROUTE);

        if ($user instanceof UserInterface) {
            $this->redirectAuthenticatedUser($event, $route);
        } else {
            $this->redirectUnauthenticatedUser($event, $route);
        }
    }

    private function redirectAuthenticatedUser(RequestEvent $event, ?string $route): void
    {
        if (in_array($route, [self::LOGIN_ROUTE, self::REGISTER_ROUTE])) {
            $event->setResponse(new RedirectResponse($this->router->generate(self::HOMEPAGE_ROUTE)));
        }
    }

    private function redirectUnauthenticatedUser(RequestEvent $event, ?string $route): void
    {
        if ($route === self::HOMEPAGE_ROUTE) {
            $event->setResponse(new RedirectResponse($this->router->generate(self::LOGIN_ROUTE)));
        }
    }
}