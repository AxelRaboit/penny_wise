<?php

namespace App\EventListener;

use App\Entity\Budget;
use DateInterval;
use DateMalformedPeriodStringException;
use DatePeriod;
use DateTime;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: FormEvents::PRE_SET_DATA, method: 'onPreSetData')]
#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class TransactionForBudgetListener
{


    /**
     * @throws DateMalformedPeriodStringException
     */
    #[NoReturn] public function onPreSetData(FormEvent $event, Budget $budget): void
    {
        $form = $event->getForm();
        $startDateFromBudget = $budget->getStartDate();
        $endDateFromBudget = $budget->getEndDate();


        $dateIntervalPeriod = new DatePeriod($startDateFromBudget, new DateInterval('P1D'), $endDateFromBudget->modify('+1 day'));

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

    #[AsEventListener(FormEvents::POST_SUBMIT)]
    public function onPostSubmit(FormEvent $event, Budget $budget): void
    {
        $form = $event->getForm();
        $transaction = $event->getData();

        $day = $form->get('date')->getData();

        $startDateFromBudget = $budget->getStartDate();
        $month = $startDateFromBudget->format('m');
        $year = $startDateFromBudget->format('Y');

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
