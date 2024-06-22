<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
final readonly class RedirectAuthenticatedUserListener
{
    private const LOGIN_ROUTE = 'app_login';
    private const HOMEPAGE_ROUTE = 'app_homepage';
    private const REGISTER_ROUTE = 'app_register';

    public function __construct(private Security $security, private RouterInterface $router){}


    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $user = $this->security->getUser();

        if ($user && in_array($request->attributes->get('_route'), [self::LOGIN_ROUTE, self::REGISTER_ROUTE])) {
            $event->setResponse(new RedirectResponse($this->router->generate(self::HOMEPAGE_ROUTE)));
        }
    }

}