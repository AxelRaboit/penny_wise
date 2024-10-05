<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Account;
use App\Repository\Account\AccountRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final readonly class AccountUpdateListener
{
    public function __construct(private AccountRepository $accountRepository) {}

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data instanceof Account) {
            return;
        }

        $name = $data->getName();
        $user = $data->getIndividual();

        $existingAccount = $this->accountRepository->findOneBy(['name' => $name, 'individual' => $user]);

        if (null !== $existingAccount && $existingAccount->getId() !== $data->getId()) {
            $form->get('name')->addError(new FormError('You already have an account with this name.'));
        }
    }
}
