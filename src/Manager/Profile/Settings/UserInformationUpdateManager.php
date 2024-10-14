<?php

declare(strict_types=1);

namespace App\Manager\Profile\Settings;

use App\Entity\UserInformation;
use App\Service\User\UserCheckerService;

final readonly class UserInformationUpdateManager
{
    public function __construct(
        private readonly UserCheckerService $userCheckerService,
    ) {}

    public function userInformationUpdate(): UserInformation
    {
        $user = $this->userCheckerService->getUserOrThrow();

        $userInformation = $user->getUserInformation() ?? new UserInformation();
        $userInformation->setUser($user);

        return $userInformation;
    }
}
