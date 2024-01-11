<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

trait PaymentMethodWithFeeTrait
{
    /** @ORM\Column(name="calculator", type="text", nullable=true) */
    #[ORM\Column(name: 'calculator', type: 'text', nullable: true)]
    protected ?string $calculator = null;

    /**
     * @ORM\ManyToOne(targetEntity="Sylius\Component\Taxation\Model\TaxCategoryInterface")
     * @ORM\JoinColumn(name="tax_category_id")
     */
    #[ORM\ManyToOne(targetEntity: TaxCategoryInterface::class)]
    #[ORM\JoinColumn(name: 'tax_category_id')]
    protected ?TaxCategoryInterface $taxCategory = null;

    /** @ORM\Column(name="calculator_configuration", type="json", nullable=true) */
    #[ORM\Column(name: 'calculator_configuration', type: 'json', nullable: true)]
    protected array $calculatorConfiguration = [];

    public function getCalculator(): ?string
    {
        return $this->calculator;
    }

    public function setCalculator(?string $calculator): void
    {
        $this->calculator = $calculator;
    }

    public function getCalculatorConfiguration(): array
    {
        return $this->calculatorConfiguration;
    }

    public function setCalculatorConfiguration(array $calculatorConfiguration): void
    {
        $this->calculatorConfiguration = $calculatorConfiguration;
    }

    public function getTaxCategory(): ?TaxCategoryInterface
    {
        return $this->taxCategory;
    }

    public function setTaxCategory(?TaxCategoryInterface $taxCategory): void
    {
        $this->taxCategory = $taxCategory;
    }
}
