<?php

namespace App\Manager\User\Settings;

use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserSettingsManager
{
    public function __construct(private EntityManagerInterface $entityManager){}

    /**
     * Toggle the sidebar collapsed state.
     *
     * @param UserSettings $userSettings
     * @return bool The updated collapsed state.
     */
    public function toggleSidebar(UserSettings $userSettings): bool
    {
        $isCollapsed = !$userSettings->isSideMenuCollapse();
        $userSettings->setSideMenuCollapse($isCollapsed);

        $this->entityManager->persist($userSettings);
        $this->entityManager->flush();

        return $isCollapsed;
    }
}
