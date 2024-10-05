<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UserGetOrThrowService
{
    public function __construct(
        private readonly Security $security
    ) {}

    public function get(): User
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        return $user;
    }
}
