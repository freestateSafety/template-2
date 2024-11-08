<?php
namespace App\Model\CreditCard;

use App\Model\CreditCard;

class Square extends CreditCard
{
    private $nonce;

    public function getNonce()
    {
        return $this->nonce;
    }

    public function setNonce($nonce)
    {
        $this->nonce = $nonce;

        return $this;
    }
}
