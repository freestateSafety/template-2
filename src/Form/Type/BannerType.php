<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bannerImage', \Symfony\Component\Form\Extension\Core\Type\FileType::class, ['label' => 'Choose banner image']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => \App\Entity\BannerImage::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'bannerImage';
    }
}
