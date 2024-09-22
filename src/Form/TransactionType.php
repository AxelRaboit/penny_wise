<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\Wallet;
use App\Repository\TransactionCategoryRepository;
use Doctrine\ORM\QueryBuilder;
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
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                'autocomplete' => true,
            ])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
