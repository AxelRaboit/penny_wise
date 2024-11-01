<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Service\Security\LoginAttemptService;
use DateMalformedStringException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
final readonly class RedirectAuthenticatedUserListener
{
    private const string LOGIN_ROUTE = 'app_login';

    private const string ACCOUNT_LIST_ROUTE = 'account_list';

    private const string REGISTER_ROUTE = 'app_register';

    private const string ROUTE = '_route';

    private const string WDT_ROUTE = '_wdt';

    private const string PROFILER_ROUTE = '_profiler';

    public function __construct(
        private Security $security,
        private RouterInterface $router,
        private LoginAttemptService $loginAttemptService
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        /** @var User|null $user */
        $user = $this->security->getUser();
        /** @var string|null $route */
        $route = $request->attributes->get(self::ROUTE);

        if (in_array($route, [self::WDT_ROUTE, self::PROFILER_ROUTE], true)) {
            return;
        }

        if ($user instanceof UserInterface) {
            if ($this->loginAttemptService->isBlocked($user)) {
                if (self::LOGIN_ROUTE !== $route) {
                    $event->setResponse(new RedirectResponse($this->router->generate(self::LOGIN_ROUTE)));
                }

                return;
            }

            $this->redirectAuthenticatedUser($event, $route);
        }
    }

    private function redirectAuthenticatedUser(RequestEvent $event, ?string $route): void
    {
        if (in_array($route, [self::LOGIN_ROUTE, self::REGISTER_ROUTE], true)) {
            $event->setResponse(new RedirectResponse($this->router->generate(self::ACCOUNT_LIST_ROUTE)));
        }
    }
}
