<?php

declare(strict_types=1);

namespace App\Form\Transaction;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\TransactionTag;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\Transaction\TransactionCategoryRepository;
use App\Repository\Wallet\WalletRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function __construct(private readonly WalletRepository $walletRepository) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User|null $user */
        $user = $builder->getOption('user');
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User not found.');
        }

        $builder
            ->add('amount', NumberType::class, [
                'attr' => ['placeholder' => 'Choose an amount'],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('transactionCategory', EntityType::class, [
                'class' => TransactionCategory::class,
                'query_builder' => fn (TransactionCategoryRepository $repo): QueryBuilder => $repo->getAllExceptSavings(),
                'choice_label' => 'getLabel',
                'placeholder' => 'Choose a type',
                'autocomplete' => true,
            ])
            ->add('nature', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Choose a nature'],
            ])
            ->add('wallet', EntityType::class, [
                'class' => Wallet::class,
                'placeholder' => 'Choose a wallet',
                'choice_label' => 'getMonthWithYearLabel',
                'choices' => $this->walletRepository->findAllWalletByUser($user),
                'autocomplete' => true,
            ])
            ->add('tag', EntityType::class, [
                'class' => TransactionTag::class,
                'multiple' => true,
                'choice_label' => 'getName',
                'placeholder' => 'Choose a tag',
                'autocomplete' => true,
            ])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null,
        ]);
    }
}
