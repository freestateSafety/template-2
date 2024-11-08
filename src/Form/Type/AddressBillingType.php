<?php
namespace App\Form\Type;

class AddressBillingType extends AddressType
{
    public function getBlockPrefix(): string
    {
        return 'addressBilling';
    }

    public function getAddressType(): string
    {
        return \App\Entity\Address::TYPE_BILLING;
    }
}
