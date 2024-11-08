<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $years = range(date('Y'), date('Y', strtotime('+20 years')));
        $builder
            ->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 25, 'size' => 30]])
            ->add('number', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 19, 'size' => 30]])
            /*->add('type', 'choice', array(
                'choices' => array(
                    '- - Select - -' => '',
                    'Visa' => 0,
                    'Mastercard' => 1,
                    'American Express' => 2
                ),
            ))*/
            ->add('expire_month', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['choices' => ['- - Select - -' => '', 'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12]])
            ->add('expire_year', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['choices' => array_combine(
                ['- - Select - -', ...$years],
                ['', ...$years]
            )])
            ->add('cvv', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 4, 'size' => 5]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => \App\Model\CreditCard::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'payment';
    }
}
