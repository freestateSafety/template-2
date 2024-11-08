<?php
/**
 * Created by IntelliJ IDEA.
 * User: eric
 * Date: 8/11/16
 * Time: 12:24 AM
 */

namespace App\Form\Type;


class AddressShippingType extends AddressType
{
    public function getBlockPrefix(): string
    {
        return 'addressShipping';
    }

    public function getAddressType(): string
    {
        return \App\Entity\Address::TYPE_SHIPPING;
    }
}
