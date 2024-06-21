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
    public function __construct(private Security $security, private RouterInterface $router){}


    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $user = $this->security->getUser();

        if ($user && in_array($request->attributes->get('_route'), ['app_login', 'app_register'])) {
            $event->setResponse(new RedirectResponse($this->router->generate('app_homepage')));
        }
    }

}