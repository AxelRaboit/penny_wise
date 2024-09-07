<?php

namespace App\Form;

use App\Entity\Budget;
use App\Enum\MonthEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', IntegerType::class, [
                'attr' => ['placeholder' => 'Choose a year'],
            ])
            ->add('month', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn(MonthEnum $month) => $month->getName(), MonthEnum::all()),
                    MonthEnum::all()
                ),
                'placeholder' => 'Choose a month',
                'choice_value' => fn(?MonthEnum $month) => $month?->value,
            ])
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('currency', TextType::class, [
                'attr' => ['placeholder' => 'Choose a currency'],
            ])
            ->add('start_balance', NumberType::class, [
                'attr' => ['placeholder' => 'Choose a start balance'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Budget::class,
        ]);
    }
}