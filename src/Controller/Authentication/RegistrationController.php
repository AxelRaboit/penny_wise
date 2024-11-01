<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Manager\Authentication\RegistrationManager;
use App\Service\Security\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    public function __construct(private readonly RegistrationManager $registrationManager, private readonly RegistrationService $registrationService) {}

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        ['user' => $user, 'form' => $form] = $this->registrationService->createFormWithUser($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->registrationManager->onUserRegistered($form, $user);

            return $this->redirectToRoute('account_list');
        }

        return $this->render('authentication/registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
