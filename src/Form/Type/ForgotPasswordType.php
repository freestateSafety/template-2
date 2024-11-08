<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class, ['attr' => ['maxlength' => 200, 'size' => 30]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $collectionConstraints = new Collection(['email' => [new NotBlank(['message' => 'E-Mail Address is required to reset your password']), new Email()]]);

        $resolver->setDefaults(['constraints' => $collectionConstraints]);
    }

    public function getBlockPrefix(): string
    {
        return 'forgot_password';
    }
}
