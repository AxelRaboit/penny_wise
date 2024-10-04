<?php

declare(strict_types=1);

namespace App\Form\Transaction;

use App\Entity\TransactionTag;
use App\EventListener\TransactionTagListener;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class TransactionTagType extends AbstractType
{
    public function __construct(private readonly TransactionTagListener $transactionTagListener) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter tag name',
                    'class' => 'rounded-md shadow-sm p-2 border border-gray-300 focus:ring-primary focus:border-primary',
                ],
                'label' => 'Tag Name',
                'label_attr' => ['class' => 'text-gray-700 font-semibold'],
            ])
            ->add('useDefaultColor', CheckboxType::class, [
                'label' => 'Use default color',
                'required' => false,
            ])
            ->add('color', ColorType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^#[0-9a-fA-F]{6}$/',
                        'message' => 'Please enter a valid color in #rrggbb format.',
                    ]),
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->transactionTagListener->onPostSubmit(...));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransactionTag::class,
        ]);
    }
}
