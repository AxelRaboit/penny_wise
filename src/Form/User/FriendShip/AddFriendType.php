<?php

declare(strict_types=1);

namespace App\Form\User\FriendShip;

use App\Entity\User;
use App\Repository\Profile\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddFriendType extends AbstractType
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security
    ) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $builder->add('username', EntityType::class, [
            'attr' => [
                'placeholder' => 'Enter username or email...',
            ],
            'class' => User::class,
            'choice_label' => fn (User $user): string => sprintf('%s (%s)', $user->getUsername(), $user->getEmail()),
            'autocomplete' => true,
            'placeholder' => 'Search by username or email',
            'query_builder' => fn (): QueryBuilder => $this->userRepository->getUsersExcludingCurrentUser($currentUser),
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
