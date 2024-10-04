<?php

declare(strict_types=1);

namespace App\Form\Profile;

use App\Entity\User;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hasAvatar = $builder->getOption('has_avatar');

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'attr' => ['placeholder' => 'Enter your email address'],
            ])
            ->add('username', TextType::class, [
                'label' => 'Username',
                'attr' => ['placeholder' => 'Enter your username'],
            ])
            ->add('userInformation', UserInformationType::class, [
                'label' => false,
                'has_avatar' => $hasAvatar,
                'required' => false,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'has_avatar' => false,
        ]);
    }
}
