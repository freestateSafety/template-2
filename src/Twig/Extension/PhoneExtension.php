<?php

namespace App\Twig\Extension;

use \Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PhoneExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [new TwigFilter('phone', fn($number) => sprintf('(%s) %s-%s', substr((string) $number, -10, 3), substr((string) $number, -7, 3), substr((string) $number, -4)))];
    }

    public function getName()
    {
        return 'app.twig.extension.phone';
    }
}
