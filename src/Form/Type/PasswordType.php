<?php
namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

trait PasswordType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, ['type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, 'first_options' => ['attr' => ['maxlength' => 255, 'size' => 30], 'label' => 'Password'], 'second_options' => ['attr' => ['maxlength' => 255, 'size' => 30], 'label' => 'Confirm Password']]);
    }
}
