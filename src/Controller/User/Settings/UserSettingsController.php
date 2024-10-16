<?php

namespace App\Controller\User\Settings;

use App\Entity\User;
use App\Manager\User\Settings\UserSettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserSettingsController extends AbstractController
{
    private UserSettingsManager $userSettingsManager;

    public function __construct(UserSettingsManager $userSettingsManager)
    {
        $this->userSettingsManager = $userSettingsManager;
    }

    #[Route('/user-settings/toggle-sidebar', name: 'user_settings_toggle_sidebar', methods: ['POST'])]
    public function toggleSidebar(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $userSettings = $user->getUserSettings();

        if (!$userSettings) {
            return new JsonResponse(['error' => 'User settings not found'], 404);
        }

        $isCollapsed = $this->userSettingsManager->toggleSidebar($userSettings);

        return new JsonResponse(['isCollapsed' => $isCollapsed]);
    }
}
