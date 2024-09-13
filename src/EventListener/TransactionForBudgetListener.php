<?php

namespace App\EventListener;

use App\Entity\Budget;
use App\Entity\Transaction;
use DateInterval;
use DateMalformedPeriodStringException;
use DateMalformedStringException;
use DatePeriod;
use DateTime;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: FormEvents::PRE_SET_DATA, method: 'onPreSetData')]
#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final class TransactionForBudgetListener
{
    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedPeriodStringException
     */
    public function onPreSetData(FormEvent $event, Budget $budget): void
    {
        $form = $event->getForm();
        $startDateFromBudget = $budget->getStartDate();

        $endDateFromBudget = DateTime::createFromInterface($budget->getEndDate())->modify('+1 day');

        $dateIntervalPeriod = new DatePeriod($startDateFromBudget, new DateInterval('P1D'), $endDateFromBudget);

        $days = [];
        foreach ($dateIntervalPeriod as $date) {
            $day = $date->format('d');
            $days[$day] = $day;
        }

        $form->add('date', ChoiceType::class, [
            'label' => 'Day',
            'choices' => $days,
            'multiple' => false,
            'mapped' => false,
        ]);
    }

    public function onPostSubmit(FormEvent $event, Budget $budget): void
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

        $startDateFromBudget = $budget->getStartDate();
        $month = (int) $startDateFromBudget->format('m');
        $year = (int) $startDateFromBudget->format('Y');

        try {
            $fullDate = new DateTime(sprintf('%d-%02d-%02d', $year, $month, $day));

            if ($fullDate < $budget->getStartDate() || $fullDate > $budget->getEndDate()) {
                $form->get('date')->addError(new FormError(
                    sprintf(
                        'The selected day must be between day %s (%s) and day %s (%s)',
                        $budget->getStartDate()->format('d'),
                        $budget->getStartDate()->format('Y-m-d'),
                        $budget->getEndDate()->format('d'),
                        $budget->getEndDate()->format('Y-m-d')
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
