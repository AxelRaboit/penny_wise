<?php

declare(strict_types=1);

namespace App\Form\Wallet;

use App\Entity\Wallet;
use App\Enum\Wallet\CurrencyEnum;
use App\EventListener\WalletCreateForYearListener;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WalletCreateForYearType extends AbstractType
{
    public function __construct(private readonly WalletCreateForYearListener $walletCreateForYearListener) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currency', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn (CurrencyEnum $currency): string => $currency->getLabel(), CurrencyEnum::cases()),
                    CurrencyEnum::cases()
                ),
                'placeholder' => 'Choose a currency',
                'autocomplete' => true,
            ])
            ->add('start_balance', NumberType::class, [
                'attr' => ['placeholder' => 'Choose a start balance'],
            ])
            ->add('spending_limit', NumberType::class, [
                'attr' => ['placeholder' => 'Choose a spending limit'],
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, $this->walletCreateForYearListener->onPreSetData(...))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->walletCreateForYearListener->onPostSubmit(...));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wallet::class,
        ]);
    }
}
