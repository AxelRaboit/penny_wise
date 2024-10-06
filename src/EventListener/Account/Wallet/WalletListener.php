<?php

declare(strict_types=1);

namespace App\EventListener\Account\Wallet;

use App\Entity\Wallet;
use App\Enum\Wallet\MonthEnum;
use App\Service\Checker\Wallet\WalletCheckerService;
use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::PRE_SET_DATA, method: 'onPreSetData')]
#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class WalletListener
{
    public function __construct(private WalletCheckerService $walletCheckerService) {}

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $months = array_combine(
            array_map(fn (MonthEnum $month): string => $month->getName(), MonthEnum::all()),
            array_map(fn (MonthEnum $month) => $month->value, MonthEnum::all())
        );

        $form->add('month', ChoiceType::class, [
            'choices' => $months,
            'multiple' => false,
            'mapped' => false,
            'required' => true,
            'autocomplete' => true,
            'attr' => [
                'required' => 'required',
            ],
        ]);
    }

    public function onPostSubmit(FormEvent $event): void
    {
        /** @var Wallet $wallet */
        $wallet = $event->getData();
        $form = $event->getForm();

        $month = $form->get('month')->getData();
        $year = $form->get('year')->getData();
        $account = $wallet->getAccount();
        $accountId = $account->getId();

        if (!is_numeric($month) || !is_numeric($year)) {
            $form->addError(new FormError('Please select a valid month and year.'));

            return;
        }

        try {
            $this->walletCheckerService->ensureWalletDoesNotExist((int) $accountId, (int) $year, (int) $month);
        } catch (LogicException $logicException) {
            $form->addError(new FormError($logicException->getMessage()));

            return;
        }

        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
        if (!$startDate) {
            $form->addError(new FormError('Invalid date selection.'));

            return;
        }

        $endDate = $startDate->modify('last day of this month');

        $wallet->setStartDate($startDate);
        $wallet->setEndDate($endDate);

        $this->setMonthForWallet($wallet, $startDate);
    }

    private function setMonthForWallet(Wallet $wallet, DateTimeInterface $startDate): void
    {
        $wallet->setMonth((int) $startDate->format('m'));
    }
}
