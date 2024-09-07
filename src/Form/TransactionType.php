<?php

namespace App\Form;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Repository\TransactionCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'attr' => ['placeholder' => 'Choose a description'],
            ])
            ->add('amount', NumberType::class, [
                'attr' => ['placeholder' => 'Choose an amount'],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('transactionCategory', EntityType::class, [
                'class' => TransactionCategory::class,
                'query_builder' => function (TransactionCategoryRepository $repo) {
                    return $repo->getAllExceptSavings();
                },
                'choice_label' => 'getLabel',
                'placeholder' => 'Choose a type',
            ])
            ->add('category', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Choose a category'],
            ])
            ->add('budget', EntityType::class, [
                'class' => Budget::class,
                'choice_label' => 'getMonthLabel',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}