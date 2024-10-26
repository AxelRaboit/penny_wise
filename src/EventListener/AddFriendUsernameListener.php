<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Repository\Profile\UserRepository;
use App\Repository\User\Friendship\FriendshipRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class AddFriendUsernameListener
{
    public function __construct(
        private UserRepository $userRepository,
        private FriendshipRepository $friendshipRepository,
        private Security $security
    ) {}

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $usernameOrEmailData = $form->get('friend_search')->getData();

        $usernameOrEmail = is_string($usernameOrEmailData) ? trim($usernameOrEmailData) : '';

        // TODO AXEL: refacto the usage of 'O' and '' by creating Util class
        if ('' === $usernameOrEmail || '0' === $usernameOrEmail) {
            $form->get('friend_search')->addError(new FormError('Username or email cannot be empty.'));

            return;
        }

        $isEmail = false !== filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL);
        $user = $isEmail
            ? $this->userRepository->findOneBy(['email' => $usernameOrEmail])
            : $this->userRepository->findOneBy(['username' => $usernameOrEmail]);

        if (null === $user) {
            $form->get('friend_search')->addError(new FormError('This username or email does not exist.'));

            return;
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if ($this->friendshipRepository->isFriend($currentUser, $user)) {
            $form->get('friend_search')->addError(new FormError('This user is already your friend.'));

            return;
        }

        if ($this->friendshipRepository->isFriendRequestPending($currentUser, $user)) {
            $form->get('friend_search')->addError(new FormError('A friend request is already pending with this user.'));
        }
    }
}
