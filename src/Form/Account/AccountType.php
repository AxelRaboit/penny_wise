<?php

declare(strict_types=1);

namespace App\Form\Account;

use App\Entity\Account;
use App\EventListener\AccountUpdateListener;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function __construct(private readonly AccountUpdateListener $accountUpdateListener) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => 'Name'],
                'label' => 'Name',
            ])
            ->add('priority', IntegerType::class, [
                'attr' => ['placeholder' => 'Priority'],
                'label' => 'Priority',
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->accountUpdateListener->onPostSubmit(...));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}
