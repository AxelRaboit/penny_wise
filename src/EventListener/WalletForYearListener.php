<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Repository\WalletRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::PRE_SET_DATA, method: 'onPreSetData')]
#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class WalletForYearListener
{
    public function __construct(private WalletRepository $walletRepository) {}

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $months = array_combine(
            array_map(fn (MonthEnum $month): string => $month->getName(), MonthEnum::all()),
            MonthEnum::cases()
        );

        $form->add('month', ChoiceType::class, [
            'choices' => $months,
            'choice_value' => fn (?MonthEnum $month) => $month instanceof MonthEnum ? $month->value : '',
            'choice_label' => fn (MonthEnum $month): string => $month->getName(),
            'placeholder' => 'Choose a month',
            'required' => true,
            'autocomplete' => true,
        ]);
    }

    public function onPostSubmit(FormEvent $event): void
    {
        /** @var Wallet $wallet */
        $wallet = $event->getData();
        $form = $event->getForm();

        $monthEnum = $form->get('month')->getData();
        $year = $wallet->getYear();

        if (!$monthEnum instanceof MonthEnum) {
            $form->addError(new FormError('Invalid month selected.'));

            return;
        }

        $existingWallet = $this->walletRepository->findOneBy(['year' => $year, 'month' => $monthEnum]);
        if (null !== $existingWallet) {
            $form->get('month')->addError(new FormError('A wallet already exists for the selected month and year.'));

            return;
        }

        try {
            $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $monthEnum->value));
            $endDate = $startDate->modify('last day of this month');

            $wallet->setStartDate($startDate);
            $wallet->setEndDate($endDate);
        } catch (Exception $exception) {
            $form->addError(new FormError('Invalid date selection: '.$exception->getMessage()));
        }
    }
}