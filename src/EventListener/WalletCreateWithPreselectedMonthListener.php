<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Wallet;
use App\Repository\Wallet\WalletRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class WalletCreateWithPreselectedMonthListener
{
    public function __construct(private WalletRepository $walletRepository) {}

    public function onPostSubmit(FormEvent $event): void
    {
        /** @var Wallet $wallet */
        $wallet = $event->getData();
        $form = $event->getForm();

        if ($this->walletExists($wallet)) {
            $form->addError(new FormError('A wallet for this month and year already exists.'));

            return;
        }

        try {
            $this->setWalletDates($wallet);
        } catch (Exception $exception) {
            $form->addError(new FormError('Invalid date selection: '.$exception->getMessage()));
        }
    }

    /**
     * Check if a wallet already exists for the given year and month.
     */
    private function walletExists(Wallet $wallet): bool
    {
        $account = $wallet->getAccount();
        $year = $wallet->getYear();
        $month = $wallet->getMonth();

        $accountId = $account->getId();
        if (null === $accountId) {
            throw new NotFoundResourceException('Account ID cannot be null');
        }

        $existingWallet = $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $month);

        return $existingWallet instanceof Wallet;
    }

    /**
     * Defines the start and end dates of the wallet based on the given year and month.
     *
     * @throws Exception
     */
    private function setWalletDates(Wallet $wallet): void
    {
        $monthValue = $wallet->getMonth();
        $year = $wallet->getYear();

        $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $monthValue));
        $endDate = $startDate->modify('last day of this month');

        $wallet->setStartDate($startDate);
        $wallet->setEndDate($endDate);
    }
}
