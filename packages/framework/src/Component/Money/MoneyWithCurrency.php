<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class MoneyWithCurrency
{
    /**
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $amount;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed" = true})
     */
    protected $currency;

    public function __toString(): string
    {
        return $this->amount . ' ' . $this->currency;
    }
}
