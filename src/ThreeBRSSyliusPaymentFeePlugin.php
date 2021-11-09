<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ThreeBRSSyliusPaymentFeePlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DependencyInjection\Compiler\RegisterFeeCalculatorsPass());
    }
}
