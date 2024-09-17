<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Wallet;
use App\Enum\CurrencyEnum;
use App\EventListener\WalletListener;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WalletType extends AbstractType
{
    public function __construct(private readonly WalletListener $walletListener) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', IntegerType::class, [
                'attr' => ['placeholder' => 'Choose a year'],
            ])
            /*->add('month', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn (MonthEnum $month): string => $month->getName(), MonthEnum::all()),
                    MonthEnum::all()
                ),
                'placeholder' => 'Choose a month',
                'choice_value' => fn (?MonthEnum $month) => $month?->value,
            ])*/
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn (CurrencyEnum $currency): string => $currency->getLabel(), CurrencyEnum::cases()),
                    CurrencyEnum::cases()
                ),
                'placeholder' => 'Choose a currency',
                'choice_value' => fn (?CurrencyEnum $currency) => $currency?->value,
            ])
            ->add('start_balance', NumberType::class, [
                'attr' => ['placeholder' => 'Choose a start balance'],
            ])
            ->add('spending_limit', NumberType::class, [
                'attr' => ['placeholder' => 'Choose a spending limit'],
                'required' => false,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $this->walletListener->onPostSubmit($event);
            });
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wallet::class,
        ]);
    }
}
