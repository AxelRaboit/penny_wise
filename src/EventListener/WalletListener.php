<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Wallet;
use App\Enum\MonthEnum;
use DateTimeInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
class WalletListener
{
    public function onPostSubmit(FormEvent $event): void
    {
        /** @var Wallet $wallet */
        $wallet = $event->getData();
        $form = $event->getForm();

        $startDate = $wallet->getStartDate();
        $endDate = $wallet->getEndDate();
        $startBalance = $wallet->getStartBalance();

        if (!$this->isSameMonth($startDate, $endDate)) {
            $form->addError(new FormError('The start date and end date must be within the same month.'));

            return;
        }

        if (!$this->isFirstDayOfMonth($startDate)) {
            $form->addError(new FormError('The start date must be the 1st of the month.'));

            return;
        }

        if (!$this->isLastDayOfMonth($endDate)) {
            $form->addError(new FormError(sprintf('The end date must be the %dth of the month.', $this->getLastDayOfMonth($startDate))));

            return;
        }

        $this->setMonthForWallet($wallet, $startDate);
    }

    private function isSameMonth(DateTimeInterface $startDate, DateTimeInterface $endDate): bool
    {
        return $startDate->format('Y-m') === $endDate->format('Y-m');
    }

    private function isFirstDayOfMonth(DateTimeInterface $startDate): bool
    {
        return 1 === (int) $startDate->format('d');
    }

    private function isLastDayOfMonth(DateTimeInterface $endDate): bool
    {
        return (int) $endDate->format('d') === $this->getLastDayOfMonth($endDate);
    }

    private function getLastDayOfMonth(DateTimeInterface $date): int
    {
        return (int) $date->format('t');
    }

    private function setMonthForWallet(Wallet $wallet, DateTimeInterface $startDate): void
    {
        $wallet->setMonth(MonthEnum::from((int) $startDate->format('m')));
    }
}
