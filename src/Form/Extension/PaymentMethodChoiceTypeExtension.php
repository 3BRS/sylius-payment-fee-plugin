<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Form\Extension;

use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodChoiceType;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator\CalculatorInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;

class PaymentMethodChoiceTypeExtension extends AbstractTypeExtension
{
    /** @var ServiceRegistryInterface */
    private $calculatorRegistry;

    public function __construct(
        ServiceRegistryInterface $calculatorRegistry
    ) {
        $this->calculatorRegistry = $calculatorRegistry;
    }

    /**
     * @param array<mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (!isset($options['subject'])) {
            return;
        }

        $subject = $options['subject'];
        $paymentCosts = [];

        foreach ($view->vars['choices'] as $choiceView) {
            $method = $choiceView->data;

            if (!$method instanceof PaymentMethodWithFeeInterface) {
                throw new UnexpectedTypeException($method, PaymentMethodWithFeeInterface::class);
            }

            if ($method->getCalculator() === null) {
                $paymentCosts[$choiceView->value] = 0;

                continue;
            }

            $calculator = $this->calculatorRegistry->get($method->getCalculator());
            assert($calculator instanceof CalculatorInterface);

            $paymentCosts[$choiceView->value] = $calculator->calculate($subject, $method->getCalculatorConfiguration());
        }

        $view->vars['payment_costs'] = $paymentCosts;
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            PaymentMethodChoiceType::class,
        ];
    }
}
