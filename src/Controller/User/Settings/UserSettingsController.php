<?php

declare(strict_types=1);

namespace App\Controller\User\Settings;

use App\Entity\User;
use App\Manager\User\Settings\UserSettingsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserSettingsController extends AbstractController
{
    public function __construct(private readonly UserSettingsManager $userSettingsManager) {}

    #[Route('/user-settings/toggle-sidebar', name: 'user_settings_toggle_sidebar', methods: ['POST'])]
    public function toggleSidebar(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $userSettings = $user->getUserSettings();
        if (!$userSettings) {
            return new JsonResponse(['error' => 'User settings not found'], Response::HTTP_NOT_FOUND);
        }

        $isCollapsed = $this->userSettingsManager->toggleSidebar($userSettings);

        return new JsonResponse(['isCollapsed' => $isCollapsed]);
    }
}
