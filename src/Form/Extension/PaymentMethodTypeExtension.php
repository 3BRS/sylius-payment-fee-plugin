<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Form\Extension;

use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodType as SyliusPaymentMethodType;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Registry\FormTypeRegistryInterface;
use Sylius\Bundle\TaxationBundle\Form\Type\TaxCategoryChoiceType;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use ThreeBRS\SyliusPaymentFeePlugin\Form\Type\CalculatorChoiceType;
use ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator\CalculatorInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;

class PaymentMethodTypeExtension extends AbstractTypeExtension
{
    public function __construct(private ServiceRegistryInterface $calculatorRegistry, private FormTypeRegistryInterface $formTypeRegistry)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber())
            ->add('taxCategory', TaxCategoryChoiceType::class)
            ->add(
                'calculator',
                CalculatorChoiceType::class,
                [
                    'label' => 'threebrs.form.payment_method.calculator',
                ],
            )->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $method = $event->getData();

                    if ($method === null || $method->getId() === null) {
                        return;
                    }

                    if ($method instanceof PaymentMethodWithFeeInterface && $method->getCalculator() !== null) {
                        $this->addConfigurationField($event->getForm(), $method->getCalculator());
                    }
                },
            )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $data = $event->getData();

                    if (!is_array($data) || empty($data) || !array_key_exists('calculator', $data)) {
                        return;
                    }

                    $this->addConfigurationField($event->getForm(), $data['calculator']);
                },
            )
        ;

        $prototypes = [];
        foreach ($this->calculatorRegistry->all() as $name => $calculator) {
            assert($calculator instanceof CalculatorInterface);
            $calculatorType = $calculator->getType();

            if (!$this->formTypeRegistry->has($calculatorType, 'default')) {
                continue;
            }

            $form = $builder->create(
                'calculatorConfiguration',
                $this->formTypeRegistry->get($calculatorType, 'default'),
            );

            $prototypes['calculators'][$name] = $form->getForm();
        }

        $builder->setAttribute('prototypes', $prototypes);
    }

    private function addConfigurationField(FormInterface $form, string $calculatorName): void
    {
        $calculator = $this->calculatorRegistry->get($calculatorName);
        assert($calculator instanceof CalculatorInterface);

        $calculatorType = $calculator->getType();
        if (!$this->formTypeRegistry->has($calculatorType, 'default')) {
            return;
        }

        $form->add('calculatorConfiguration', $this->formTypeRegistry->get($calculatorType, 'default'));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['prototypes'] = [];
        foreach ($form->getConfig()->getAttribute('prototypes') as $group => $prototypes) {
            foreach ($prototypes as $type => $prototype) {
                $view->vars['prototypes'][$group . '_' . $type] = $prototype->createView($view);
            }
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            SyliusPaymentMethodType::class,
        ];
    }
}
