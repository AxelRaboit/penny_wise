<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Enum\TransactionCategoryEnum;
use DateInterval;
use DateMalformedPeriodStringException;
use DateMalformedStringException;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use function Symfony\Component\String\u;

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
        /** @var Transaction|null $transaction */
        $transaction = $event->getData();

        $days = $this->getAvailableDays($wallet);
        $selectedDay = $this->getSelectedDay($transaction);

        $this->addDateFieldToForm($form, $days, $selectedDay);
    }

    public function onPostSubmit(FormEvent $event, Wallet $wallet): void
    {
        $form = $event->getForm();
        /** @var Transaction|null $transaction */
        $transaction = $event->getData();
        if (!$transaction instanceof Transaction) {
            return;
        }

        $this->handleBudgetField($form, $transaction);
        $this->handleDateField($form, $transaction, $wallet);
    }

    private function handleBudgetField(FormInterface $form, Transaction $transaction): void
    {
        $transactionCategory = $transaction->getTransactionCategory();
        $transactionCategoryName = u($transactionCategory->getName())->lower();

        if ($transactionCategoryName->equalsTo(TransactionCategoryEnum::Incomes->value)) {
            $transaction->setBudget(null);
            $transaction->setBudgetDefinedThroughAmount(null);

            return;
        }

        if ($form->has('budgetDefinedThroughAmount')) {
            $defineBudgetThroughAmount = $form->get('budgetDefinedThroughAmount')->getData();
            $budget = $form->get('budget')->getData();

            if ($defineBudgetThroughAmount) {
                $transaction->setBudget((string) $transaction->getAmount());
            } elseif (is_numeric($budget)) {
                $transaction->setBudget((string) $budget);
            } else {
                $transaction->setBudget(null);
            }
        }
    }

    private function handleDateField(FormInterface $form, Transaction $transaction, Wallet $wallet): void
    {
        $day = $form->get('date')->getData();

        if (null === $day) {
            $transaction->setDate(null);

            return;
        }

        if (!is_numeric($day)) {
            $form->get('date')->addError(new FormError('The selected day is invalid.'));

            return;
        }

        $fullDate = $this->buildFullDate((int) $day, $wallet);
        if (!$fullDate instanceof DateTime) {
            return;
        }

        if ($this->isDateOutOfBounds($fullDate, $wallet)) {
            $this->addDateOutOfBoundsError($form, $wallet);

            return;
        }

        $transaction->setDate($fullDate);
    }

    private function buildFullDate(int $day, Wallet $wallet): ?DateTime
    {
        $startDateFromWallet = $wallet->getStartDate();
        $month = (int) $startDateFromWallet->format('m');
        $year = (int) $startDateFromWallet->format('Y');

        try {
            return new DateTime(sprintf('%d-%02d-%02d', $year, $month, $day));
        } catch (Exception) {
            return null;
        }
    }

    private function isDateOutOfBounds(DateTime $date, Wallet $wallet): bool
    {
        if ($date < $wallet->getStartDate()) {
            return true;
        }

        return $date > $wallet->getEndDate();
    }

    private function addDateOutOfBoundsError(FormInterface $form, Wallet $wallet): void
    {
        $form->get('date')->addError(new FormError(
            sprintf(
                'The selected day must be between day %s (%s) and day %s (%s)',
                $wallet->getStartDate()->format('d'),
                $wallet->getStartDate()->format('Y-m-d'),
                $wallet->getEndDate()->format('d'),
                $wallet->getEndDate()->format('Y-m-d')
            )
        ));
    }

    /**
     * @return array<int, string>
     *
     * @throws DateMalformedPeriodStringException
     * @throws DateMalformedStringException
     */
    private function getAvailableDays(Wallet $wallet): array
    {
        $startDateFromWallet = $wallet->getStartDate();
        $endDateFromWallet = DateTime::createFromInterface($wallet->getEndDate())->modify('+1 day');

        $dateIntervalPeriod = new DatePeriod($startDateFromWallet, new DateInterval('P1D'), $endDateFromWallet);

        $days = [];
        foreach ($dateIntervalPeriod as $date) {
            $day = (int) $date->format('d');
            $days[$day] = $date->format('d');
        }

        return $days;
    }

    /**
     * @param array<int, string> $days
     */
    private function addDateFieldToForm(FormInterface $form, array $days, ?string $selectedDay): void
    {
        $form->add('date', ChoiceType::class, [
            'choices' => $days,
            'multiple' => false,
            'mapped' => false,
            'required' => false,
            'autocomplete' => true,
            'placeholder' => 'Choose a day',
            'data' => $selectedDay,
        ]);
    }

    private function getSelectedDay(?Transaction $transaction): ?string
    {
        return $transaction instanceof Transaction && $transaction->getDate() instanceof DateTimeInterface ? $transaction->getDate()->format('d') : null;
    }
}
