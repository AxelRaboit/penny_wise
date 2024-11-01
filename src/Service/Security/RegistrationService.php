<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Entity\User;
use App\Form\Authentication\RegistrationFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class RegistrationService
{
    public function __construct(private FormFactoryInterface $formFactory) {}

    /**
     * @return array{user: User, form: FormInterface}
     */
    public function createFormWithUser(Request $request): array
    {
        $user = new User();
        $form = $this->formFactory->create(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        return ['user' => $user, 'form' => $form];
    }
}
