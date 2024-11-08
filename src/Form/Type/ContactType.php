<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['maxlength' => 30, 'size' => 30]])
            ->add('email', EmailType::class, ['attr' => ['maxlength' => 200, 'size' => 30]])
            ->add('phone', TextType::class, ['attr' => ['maxlength' => 10, 'size' => 30], 'required' => false])
            ->add('company', TextType::class, ['attr' => ['maxlength' => 50, 'size' => 30], 'required' => false])
            ->add('comments', TextareaType::class, ['attr' => ['maxlength' => 300, 'rows' => 15]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $collectionConstraint = new Collection(['name' => [new NotBlank(['message' => 'Name should not be blank']), new Length(['min' => 2])], 'email' => [new NotBlank(['message' => 'We\'d like your e-mail address so we know how to reach you']), new Email()], 'phone' => [], 'company' => [], 'comments' => [new NotBlank(['message' => 'Comments should not be blank'])]]);

        $resolver->setDefaults(['constraints' => $collectionConstraint]);
    }

    public function getName()
    {
        return 'contact';
    }
}
