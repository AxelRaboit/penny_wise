<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Transaction;
use App\Entity\Wallet;
use DateInterval;
use DateMalformedPeriodStringException;
use DateMalformedStringException;
use DatePeriod;
use DateTime;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::PRE_SET_DATA, method: 'onPreSetData')]
#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final class TransactionForWalletListener
{
    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedPeriodStringException
     */
    public function onPreSetData(FormEvent $event, Wallet $wallet): void
    {
        $form = $event->getForm();
        $startDateFromWallet = $wallet->getStartDate();

        $endDateFromWallet = DateTime::createFromInterface($wallet->getEndDate())->modify('+1 day');

        $dateIntervalPeriod = new DatePeriod($startDateFromWallet, new DateInterval('P1D'), $endDateFromWallet);

        $days = [];
        foreach ($dateIntervalPeriod as $date) {
            $day = $date->format('d');
            $days[$day] = $day;
        }

        $form->add('date', ChoiceType::class, [
            'choices' => $days,
            'multiple' => false,
            'mapped' => false,
            'required' => true,
        ]);
    }

    public function onPostSubmit(FormEvent $event, Wallet $wallet): void
    {
        $form = $event->getForm();
        $transaction = $event->getData();

        if (!$transaction instanceof Transaction) {
            return;
        }

        $day = $form->get('date')->getData();

        if (!is_numeric($day)) {
            $form->get('date')->addError(new FormError('The selected day is invalid.'));

            return;
        }

        $day = (int) $day;

        $startDateFromWallet = $wallet->getStartDate();
        $month = (int) $startDateFromWallet->format('m');
        $year = (int) $startDateFromWallet->format('Y');

        try {
            $fullDate = new DateTime(sprintf('%d-%02d-%02d', $year, $month, $day));

            if ($fullDate < $wallet->getStartDate() || $fullDate > $wallet->getEndDate()) {
                $form->get('date')->addError(new FormError(
                    sprintf(
                        'The selected day must be between day %s (%s) and day %s (%s)',
                        $wallet->getStartDate()->format('d'),
                        $wallet->getStartDate()->format('Y-m-d'),
                        $wallet->getEndDate()->format('d'),
                        $wallet->getEndDate()->format('Y-m-d')
                    )
                ));

                return;
            }

            $transaction->setDate($fullDate);
        } catch (Exception $exception) {
            $form->get('date')->addError(new FormError(
                sprintf(
                    'The selected day is invalid. Error: %s',
                    $exception->getMessage()
                )
            ));
        }
    }
}
