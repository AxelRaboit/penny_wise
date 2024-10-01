<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\TransactionTag;
use App\Entity\Wallet;
use App\EventListener\TransactionForWalletListener;
use App\Repository\TransactionCategoryRepository;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TransactionForWalletType extends AbstractType
{
    public function __construct(private readonly TransactionForWalletListener $transactionForWalletListener) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $wallet = $builder->getOption('wallet');
        $transactionCategory = $builder->getOption('transactionCategory');

        /** @var Transaction|null $transaction */
        $transaction = $builder->getOption('data');
        $isNewTransaction = null === $transaction?->getId();

        $budgetDefinedThroughAmount = $isNewTransaction ? ($transaction?->getBudgetDefinedThroughAmount() ?? true) : null;

        $builder
            ->add('amount', NumberType::class, [
                'attr' => ['placeholder' => 'Enter an amount'],
            ])
            ->add('budget', NumberType::class, [
                'label' => 'Budgeted Amount',
                'required' => false,
            ])
            ->add('transactionCategory', EntityType::class, [
                'class' => TransactionCategory::class,
                'query_builder' => fn (TransactionCategoryRepository $repo): QueryBuilder => $repo->getAllExceptSavings(),
                'choice_label' => 'getLabel',
                'placeholder' => 'Choose a type',
                'autocomplete' => true,
                'data' => $transactionCategory,
                'disabled' => null !== $transactionCategory,
            ])
            ->add('nature', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Enter a category (optional)'],
            ])
            ->add('tag', EntityType::class, [
                'class' => TransactionTag::class,
                'multiple' => true,
                'choice_label' => 'getName',
                'autocomplete' => true,
                'required' => false,
            ])
            ->add('highlight', CheckboxType::class, [
                'label' => 'Highlight it',
                'required' => false,
            ]);

        if ($isNewTransaction) {
            $builder->add('budgetDefinedThroughAmount', CheckboxType::class, [
                'label' => 'Use amount as budget',
                'required' => false,
                'data' => $budgetDefinedThroughAmount,
            ]);
        }

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($wallet): void {
                if ($wallet instanceof Wallet) {
                    $this->transactionForWalletListener->onPreSetData($event, $wallet);
                }
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($wallet): void {
                if ($wallet instanceof Wallet) {
                    $this->transactionForWalletListener->onPostSubmit($event, $wallet);
                }
            });
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'wallet' => null,
            'transactionCategory' => null,
        ]);

        $resolver->setAllowedTypes('wallet', ['null', Wallet::class]);
    }
}
