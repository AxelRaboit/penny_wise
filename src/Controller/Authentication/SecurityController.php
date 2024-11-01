<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Repository\Profile\UserRepository;
use App\Service\Security\LoginAttemptService;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    public function __construct(
        private readonly LoginAttemptService $loginAttemptService,
        private readonly UserRepository $userRepository
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedIntervalStringException
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $lastUsername]);

        if ($user) {
            if ($this->loginAttemptService->isBlocked($user)) {
                $this->addFlash('error', 'Your account is blocked for 30 minutes. Please try again later.');

                return $this->render('security/login/login.html.twig', [
                    'last_username' => $lastUsername,
                    'error' => null,
                ]);
            }

            if ($error instanceof BadCredentialsException) {
                $this->loginAttemptService->recordFailedAttempt($user);
            }
        }

        return $this->render('security/login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
