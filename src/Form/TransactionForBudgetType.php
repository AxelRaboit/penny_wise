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
use Symfony\Component\Validator\Constraints as Assert;

final class TransactionForBudgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Budget $budget */
        $budget = $builder->getOption('budget');

        $builder
            ->add('description', TextType::class, [
                'attr' => ['placeholder' => 'Enter a description'],
            ])
            ->add('amount', NumberType::class, [
                'attr' => ['placeholder' => 'Enter an amount'],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'min' => $budget->getStartDate()->format('Y-m-d'),
                    'max' => $budget->getEndDate()->format('Y-m-d'),
                ],
                'constraints' => [
                    new Assert\Range([
                        'min' => $budget->getStartDate(),
                        'max' => $budget->getEndDate(),
                        'notInRangeMessage' => 'The date must be between {{ min }} and {{ max }}.',
                    ]),
                ],
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
                'attr' => ['placeholder' => 'Enter a category (optional)'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'budget' => null,
        ]);
    }
}
