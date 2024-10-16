<?php

declare(strict_types=1);

namespace App\Manager\User\Settings;

use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserSettingsManager
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * Toggle the sidebar collapsed state.
     *
     * @return bool the updated collapsed state
     */
    public function toggleSidebar(UserSettings $userSettings): bool
    {
        $isCollapsed = true !== $userSettings->isSideMenuCollapse();
        $userSettings->setSideMenuCollapse($isCollapsed);

        $this->entityManager->persist($userSettings);
        $this->entityManager->flush();

        return $isCollapsed;
    }
}
