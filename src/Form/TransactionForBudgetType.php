<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\EventListener\TransactionForBudgetListener;
use App\Repository\TransactionCategoryRepository;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TransactionForBudgetType extends AbstractType
{
    public function __construct(private readonly TransactionForBudgetListener $transactionForBudgetListener) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $budget = $builder->getOption('budget');

        $builder
            ->add('description', TextType::class, [
                'attr' => ['placeholder' => 'Enter a description'],
            ])
            ->add('amount', NumberType::class, [
                'attr' => ['placeholder' => 'Enter an amount'],
            ])
            ->add('transactionCategory', EntityType::class, [
                'class' => TransactionCategory::class,
                'query_builder' => fn (TransactionCategoryRepository $repo): QueryBuilder => $repo->getAllExceptSavings(),
                'choice_label' => 'getLabel',
                'placeholder' => 'Choose a type',
            ])
            ->add('category', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Enter a category (optional)'],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($budget): void {
                /* @var Budget $budget */
                $this->transactionForBudgetListener->onPreSetData($event, $budget);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($budget): void {
                /* @var Budget $budget */
                $this->transactionForBudgetListener->onPostSubmit($event, $budget);
            });
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'budget' => null,
        ]);

        $resolver->setAllowedTypes('budget', ['null', Budget::class]);
    }
}
