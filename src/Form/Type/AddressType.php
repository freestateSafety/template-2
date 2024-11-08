<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', HiddenType::class, ['data' => static::getAddressType()])
            ->add('addressLine1', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 255, 'size' => 30]])
            ->add('addressLine2', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 255, 'size' => 30], 'required' => false])
            ->add('city', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 30, 'size' => 25]])
            ->add('state', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['attr' => ['size' => '1'], 'choices' => ['- - Select - -' => '', 'Alabama' => 'AL', 'Alaska' => 'AK', 'Arizona' => 'AZ', 'Arkansas' => 'AR', 'California' => 'CA', 'Colorado' => 'CO', 'Connecticut' => 'CT', 'Delaware' => 'DE', 'Florida' => 'FL', 'Georgia' => 'GA', 'Hawaii' => 'HI', 'Idaho' => 'ID', 'Illinois' => 'IL', 'Indiana' => 'IN', 'Iowa' => 'IA', 'Kansas' => 'KS', 'Kentucky' => 'KY', 'Louisiana' => 'LA', 'Maine' => 'ME', 'Maryland' => 'MD', 'Massachusetts' => 'MA', 'Michigan' => 'MI', 'Minnesota' => 'MN', 'Mississippi' => 'MS', 'Missouri' => 'MO', 'Montana' => 'MT', 'Nebraska' => 'NE', 'Nevada' => 'NV', 'New Hampshire' => 'NH', 'New Jersey' => 'NJ', 'New Mexico' => 'NM', 'New York' => 'NY', 'North Carolina' => 'NC', 'North Dakota' => 'ND', 'Ohio' => 'OH', 'Oklahoma' => 'OK', 'Oregon' => 'OR', 'Pennsylvania' => 'PA', 'Puerto Rico' => 'PR', 'Rhode Island' => 'RI', 'South Carolina' => 'SC', 'South Dakota' => 'SD', 'Tennessee' => 'TN', 'Texas' => 'TX', 'Utah' => 'UT', 'Vermont' => 'VT', 'Virginia' => 'VA', 'West Virginia' => 'WV', 'Wisconsin' => 'WI', 'Wyoming' => 'WY', 'Washington' => 'WA', 'Washington, D.C.' => 'DC'], 'choice_label' => fn($abbr, $state, $index) => empty($abbr)? $state : $abbr.' - '.$state])
            ->add('zip', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['attr' => ['maxlength' => 6, 'size' => 10]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Address::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'address';
    }

    abstract public function getAddressType(): string;
}
