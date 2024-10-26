<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Repository\Profile\UserRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class AddFriendUsernameListener
{
    public function __construct(private UserRepository $userRepository) {}

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $usernameData = $form->get('username')->getData();

        $username = is_string($usernameData) ? trim($usernameData) : '';

        /* TODO AXEL: refacto the usage of 'O' and '' by creating Util class */
        if ('' === $username || '0' === $username) {
            $form->get('username')->addError(new FormError('Username cannot be empty.'));

            return;
        }

        if (null === $this->userRepository->findOneBy(['username' => $username])) {
            $form->get('username')->addError(new FormError('This username does not exist.'));
        }
    }
}
