<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Repository\Profile\UserRepository;
use App\Repository\User\Friendship\FriendshipRepository;
use App\Util\StringHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use function Symfony\Component\String\u;

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
        $usernameOrEmail = $this->sanitizeInput($form->get('friend_search')->getData());

        $this->validateUserAndInput($usernameOrEmail, $form);
    }

    private function validateUserAndInput(string $usernameOrEmail, FormInterface $form): void
    {
        if ($this->isInputEmpty($usernameOrEmail, $form)) {
            return;
        }

        $this->processUser($usernameOrEmail, $form);
    }

    private function sanitizeInput(mixed $data): string
    {
        return is_string($data) ? u($data)->trim()->toString() : '';
    }

    private function isInputEmpty(string $input, FormInterface $form): bool
    {
        if (StringHelper::isEmpty($input)) {
            $form->get('friend_search')->addError(new FormError('Username or email cannot be empty.'));

            return true;
        }

        return false;
    }

    private function processUser(string $usernameOrEmail, FormInterface $form): void
    {
        $user = $this->findUserByUsernameOrEmail($usernameOrEmail);
        if (!$user instanceof User) {
            $form->get('friend_search')->addError(new FormError('This username or email does not exist.'));

            return;
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        !$this->isSelfRequest($user, $currentUser, $form)
        && !$this->isAlreadyFriend($currentUser, $user, $form)
        && !$this->isRequestPending($currentUser, $user, $form);
    }

    private function findUserByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        $isEmail = false !== filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL);

        return $isEmail
            ? $this->userRepository->findOneBy(['email' => $usernameOrEmail])
            : $this->userRepository->findOneBy(['username' => $usernameOrEmail]);
    }

    private function isSelfRequest(User $user, User $currentUser, FormInterface $form): bool
    {
        if ($user === $currentUser) {
            $form->get('friend_search')->addError(new FormError('You cannot send a friend request to yourself.'));

            return true;
        }

        return false;
    }

    private function isAlreadyFriend(User $currentUser, User $user, FormInterface $form): bool
    {
        if ($this->friendshipRepository->isFriend($currentUser, $user)) {
            $form->get('friend_search')->addError(new FormError('This user is already your friend.'));

            return true;
        }

        return false;
    }

    private function isRequestPending(User $currentUser, User $user, FormInterface $form): bool
    {
        if ($this->friendshipRepository->isFriendRequestPending($currentUser, $user)) {
            $form->get('friend_search')->addError(new FormError('A friend request is already pending with this user.'));

            return true;
        }

        return false;
    }
}
