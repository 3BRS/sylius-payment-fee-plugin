<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

trait PaymentMethodWithFeeTrait
{
    #[ORM\Column(name: 'calculator', type: Types::TEXT, nullable: true)]
    protected ?string $calculator = null;

    #[ORM\ManyToOne(targetEntity: TaxCategoryInterface::class)]
    #[ORM\JoinColumn(name: 'tax_category_id', nullable: true)]
    protected ?TaxCategoryInterface $taxCategory = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(name: 'calculator_configuration', type: 'json', nullable: true)]
    protected ?array $calculatorConfiguration = [];

    public function getCalculator(): ?string
    {
        return $this->calculator;
    }

    public function setCalculator(?string $calculator): void
    {
        $this->calculator = $calculator;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCalculatorConfiguration(): array
    {
        return $this->calculatorConfiguration ?? [];
    }

    /**
     * @param array<string, mixed> $calculatorConfiguration
     */
    public function setCalculatorConfiguration(?array $calculatorConfiguration): void
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
