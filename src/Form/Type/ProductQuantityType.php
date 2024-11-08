<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, ['class' => \App\Entity\Product::class])
            ->add('quantity', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class)
            ->add('price', \Symfony\Component\Form\Extension\Core\Type\NumberType::class)
            ->add('label', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => \App\Entity\ProductQuantity::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'quantity';
    }
}
