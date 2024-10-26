<?php

declare(strict_types=1);

namespace App\Form\User\FriendShip;

use App\EventListener\AddFriendUsernameListener;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddFriendType extends AbstractType
{
    public function __construct(private readonly AddFriendUsernameListener $usernameListener) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('friend_search', TextType::class, [
                'attr' => ['placeholder' => 'Enter username or an email'],
                'label' => 'Username',
                'required' => true,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->usernameListener->onPostSubmit(...));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
