<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeTrait;

/**
 * Doctrine annotations are deprecated
 * You can test they still works by changing in tests/Application/config/packages/doctrine.yaml
 * configuration doctrine.orm.mappings.App.type to 'annotation'
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_method")
 */
#[ORM\Entity]
#[ORM\Table(name: "sylius_payment_method")]
class PaymentMethod extends BasePaymentMethod implements PaymentMethodWithFeeInterface
{
    use PaymentMethodWithFeeTrait;
}
