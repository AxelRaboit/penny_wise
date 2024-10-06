<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Wallet;
use App\Enum\Wallet\MonthEnum;
use App\Repository\Wallet\WalletRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

#[AsEventListener(event: FormEvents::PRE_SET_DATA, method: 'onPreSetData')]
#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class WalletCreateForYearListener
{
    public function __construct(private WalletRepository $walletRepository) {}

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();

        // Utiliser isset() pour vérifier si l'option 'disable_month' est définie
        $disableMonth = $options['disable_month'] ?? false;

        // Ajouter le champ "month" seulement si disable_month est false
        if (!$disableMonth) {
            $months = array_combine(
                array_map(fn (MonthEnum $month): string => $month->getName(), MonthEnum::all()),
                array_map(fn (MonthEnum $month): int => $month->value, MonthEnum::all())
            );

            $form->add('month', ChoiceType::class, [
                'choices' => $months,
                'choice_value' => fn (?int $month): string => null !== $month ? (string) $month : '',
                'choice_label' => fn (int $month): string => MonthEnum::from($month)->getName(),
                'placeholder' => 'Choose a month',
                'required' => true,
                'autocomplete' => true,
            ]);
        }
    }

    public function onPostSubmit(FormEvent $event): void
    {
        /** @var Wallet $wallet */
        $wallet = $event->getData();
        $form = $event->getForm();

        $monthValue = $form->get('month')->getData();
        $year = $wallet->getYear();

        if (!is_int($monthValue) || !MonthEnum::tryFrom($monthValue) instanceof MonthEnum) {
            $form->addError(new FormError('Invalid month selected.'));

            return;
        }

        $monthEnum = MonthEnum::from($monthValue);

        $account = $wallet->getAccount();
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account not found.');
        }

        $existingWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $monthEnum->value);
        if ($existingWallet instanceof Wallet) {
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
