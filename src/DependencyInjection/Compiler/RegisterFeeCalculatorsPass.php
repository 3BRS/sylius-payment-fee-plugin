<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator\DelegatingCalculator;

final class RegisterFeeCalculatorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('threebrs.sylius_payment_fee_plugin.registry.payment_calculator') ||
            !$container->hasDefinition('threebrs.sylius_payment_fee_plugin.form_registry.payment_calculator')) {
            return;
        }

        $registry = $container->getDefinition('threebrs.sylius_payment_fee_plugin.registry.payment_calculator');
        $formTypeRegistry = $container->getDefinition(
            'threebrs.sylius_payment_fee_plugin.form_registry.payment_calculator',
        );
        $calculators = [];

        foreach ($container->findTaggedServiceIds(DelegatingCalculator::class) as $id => $attributes) {
            if (!isset($attributes[0]['calculator'], $attributes[0]['label'])) {
                throw new \InvalidArgumentException(
                    'Tagged payment fee calculators needs to have `calculator` and `label` attributes.',
                );
            }

            $name = $attributes[0]['calculator'];
            $calculators[$name] = $attributes[0]['label'];

            $registry->addMethodCall('register', [$name, new Reference($id)]);

            if (isset($attributes[0]['form_type'])) {
                $formTypeRegistry->addMethodCall('add', [$name, 'default', $attributes[0]['form_type']]);
            }
        }

        $container->setParameter('threebrs.sylius_payment_fee_plugin.payment_fee_calculators', $calculators);
    }
}
