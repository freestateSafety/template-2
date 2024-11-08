<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('material', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 100, 'size' => 40]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Material::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'material';
    }
}
