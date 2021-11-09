<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentRestrictionPlugin\Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_method")
 */
class PaymentMethod extends BasePaymentMethod implements PaymentMethodWithFeeInterface
{
    use PaymentMethodWithFeeTrait;
}
