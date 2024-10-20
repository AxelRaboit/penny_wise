<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\TransactionTag;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\String\u;

#[AsEventListener(event: FormEvents::POST_SUBMIT, method: 'onPostSubmit')]
final class TransactionTagListener
{
    private const string DEFAULT_COLOR = '#1877F2';

    public function onPostSubmit(FormEvent $event): void
    {
        /** @var TransactionTag $transactionTag */
        $transactionTag = $event->getData();
        $form = $event->getForm();

        if (!$transactionTag instanceof TransactionTag) {
            return;
        }

        $transactionTag->setName(u($transactionTag->getName())->lower()->toString());

        if ($form->get('useDefaultColor')->getData()) {
            $transactionTag->setColor(self::DEFAULT_COLOR);
        }
    }
}
