<?php

declare(strict_types=1);

namespace App\Manager\Security\Authentication;

use App\Entity\Messenger;
use App\Entity\User;
use App\Entity\UserInformation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegistrationManager
{
    private const array ROLE_USER = ['ROLE_USER'];

    private const string PASSWORD_FORM_FIELD = 'plainPassword';

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager
    ) {}

    public function onUserRegistered(FormInterface $form, User $user): void
    {
        /** @var string $password */
        $password = $form->get(self::PASSWORD_FORM_FIELD)->getData();

        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $user->setRoles(self::ROLE_USER);
        $user->setActive(true);

        $userInformation = $this->createUserInformation($user);
        $messenger = $this->createMessenger($user);

        $user->setUserInformation($userInformation);
        $user->setMessenger($messenger);

        $this->entityManager->persist($user);
        $this->entityManager->persist($messenger);
        $this->entityManager->flush();
    }

    private function createUserInformation(User $user): UserInformation
    {
        $userInformation = new UserInformation();
        $userInformation->setUser($user);

        return $userInformation;
    }

    private function createMessenger(User $user): Messenger
    {
        $messenger = new Messenger();
        $messenger->setUser($user);

        return $messenger;
    }
}
