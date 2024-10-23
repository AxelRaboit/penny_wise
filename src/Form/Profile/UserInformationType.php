<?php

declare(strict_types=1);

namespace App\Form\Profile;

use App\Entity\UserInformation;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\UX\Dropzone\Form\DropzoneType;

class UserInformationType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hasAvatar = $builder->getOption('has_avatar');

        $builder
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
                'attr' => ['placeholder' => 'Enter your first name'],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['placeholder' => 'Enter your last name'],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'attr' => [
                    'placeholder' => 'Enter your bio',
                    'rows' => 5,
                ],
            ])
            ->add('avatarFile', DropzoneType::class, [
                'label' => 'Upload Avatar',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, or WEBP).',
                    ]),
                ],
                'attr' => [
                    'data-controller' => 'dropzone',
                ],
            ]);
        if ($hasAvatar) {
            $builder->add('remove_avatar', CheckboxType::class, [
                'label' => 'Remove current avatar',
                'mapped' => false,
            ]);
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserInformation::class,
            'has_avatar' => false,
        ]);
    }
}
