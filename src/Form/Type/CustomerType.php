<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 50, 'size' => 30]])
            ->add('lastName', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 50, 'size' => 30]])
            ->add('company', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 200, 'size' => 30], 'required' => false])
            ->add('phone', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 12, 'size' => 20]])
            ->add('email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class, ['attr' => ['maxlength' => 200, 'size' => 30]])
            ->add('role', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['choices' => ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], 'choice_label' => function ($value, $key, $index) {
                $value = str_replace(['ROLE_', '_'], ['', ' '], $value);
                $words = [];
                foreach (explode(' ', $value) as $word) {
                    $words[] = ucfirst(strtolower($word));
                }
                return implode(' ', $words);
            }, 'empty_data' => fn(FormInterface $form) => ($form->getData())?: 'ROLE_USER'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Customer::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'customer';
    }
}
