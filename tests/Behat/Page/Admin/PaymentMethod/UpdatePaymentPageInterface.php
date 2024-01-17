<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\UpdatePageInterface as BaseUpdatePageInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface UpdatePaymentPageInterface extends BaseUpdatePageInterface
{
    public function changeAmount(?string $value, ChannelInterface $channel): void;

    public function getAmount(ChannelInterface $channel): array|bool|string;
}
