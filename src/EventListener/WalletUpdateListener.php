<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Repository\WalletRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;

final readonly class WalletUpdateListener
{
    public function __construct(private WalletRepository $walletRepository) {}

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        /** @var Wallet $wallet */
        $wallet = $event->getData();

        $months = array_combine(
            array_map(fn (MonthEnum $month): string => $month->getName(), MonthEnum::cases()),
            array_map(fn (MonthEnum $month): int => $month->value, MonthEnum::cases())
        );

        $form->add('month', ChoiceType::class, [
            'choices' => $months,
            'choice_value' => fn (?int $month): int|string => $month ?? '',
            'choice_label' => fn (int $month): string => MonthEnum::from($month)->getName(),
            'placeholder' => 'Choose a month',
            'required' => true,
            'autocomplete' => true,
            'data' => $wallet->getMonth(),
        ]);
    }

    public function onPostSubmit(FormEvent $event): void
    {
        /** @var Wallet $wallet */
        $wallet = $event->getData();
        $form = $event->getForm();

        $monthValue = $form->get('month')->getData();

        if (!is_int($monthValue)) {
            $form->addError(new FormError('Invalid month selected.'));

            return;
        }

        $wallet->setMonth($monthValue);

        $year = $wallet->getYear();

        $existingWallet = $this->walletRepository->findOneBy(['year' => $year, 'month' => $monthValue]);
        if (null !== $existingWallet && $existingWallet->getId() !== $wallet->getId()) {
            $form->get('month')->addError(new FormError('A wallet already exists for the selected month and year.'));

            return;
        }

        try {
            $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $monthValue));
            $endDate = $startDate->modify('last day of this month');

            $wallet->setStartDate($startDate);
            $wallet->setEndDate($endDate);
        } catch (Exception $exception) {
            $form->addError(new FormError('Invalid date selection: '.$exception->getMessage()));
        }
    }
}
