<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 200, 'size' => 40], 'label' => 'Product Search'])
            ->add('limit', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, ['required' => false])
            ->setMethod('get')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $collectionConstraint = new Assert\Collection(['q' => [new Assert\NotBlank(['message' => 'Search term should not be blank'])], 'limit' => [new Assert\GreaterThan(0)]]);

        $resolver->setDefaults(['csrf_protection' => false, 'constraints' => $collectionConstraint]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
