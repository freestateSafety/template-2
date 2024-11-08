<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetType extends AbstractType
{
    use PasswordType {
        buildForm as _buildForm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (isset($options['current_password']) && $options['current_password'] === true) {
            $builder->add('current_password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, ['attr' => ['maxlength' => 255, 'size' => 30]]);
        }

        $this->_buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['current_password' => true]);
    }

    public function getBlockPrefix(): string
    {
        return 'passwordReset';
    }
}
