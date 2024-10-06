<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Wallet;
use DateTimeImmutable;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class WalletCreateWithPreselectedMonthListener
{
    public function onPostSubmit(FormEvent $event): void
    {
        /** @var Wallet $wallet */
        $wallet = $event->getData();
        $monthValue = $wallet->getMonth();
        $year = $wallet->getYear();

        try {
            $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $monthValue));
            $endDate = $startDate->modify('last day of this month');

            $wallet->setStartDate($startDate);
            $wallet->setEndDate($endDate);
        } catch (Exception $exception) {
            $event->getForm()->addError(new FormError('Invalid date selection: '.$exception->getMessage()));
        }
    }
}
