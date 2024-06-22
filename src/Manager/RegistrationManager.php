<?php

namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationManager
{
    private const ROLE_USER = ['ROLE_USER'];
    private const PASSWORD_FORM_FIELD = 'plainPassword';
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher, private readonly EntityManagerInterface $entityManager){}
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

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}