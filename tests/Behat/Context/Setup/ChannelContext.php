<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final readonly class ChannelContext implements Context
{
    public function __construct(
        private FactoryInterface $channelFactory,
        private FactoryInterface $currencyFactory,
        private FactoryInterface $localeFactory,
        private FactoryInterface $countryFactory,
        private FactoryInterface $zoneFactory,
        private FactoryInterface $zoneMemberFactory,
        private FactoryInterface $shippingMethodFactory,
        private RepositoryInterface $channelRepository,
        private RepositoryInterface $currencyRepository,
        private RepositoryInterface $localeRepository,
        private RepositoryInterface $countryRepository,
        private RepositoryInterface $zoneRepository,
        private RepositoryInterface $shippingMethodRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @Given the store operates on a single channel in :currencyCode
     */
    public function theStoreOperatesOnASingleChannel(string $currencyCode = 'USD'): void
    {
        // Check if channel already exists
        if ($this->channelRepository->findOneBy([]) !== null) {
            return;
        }

        $defaultLocale = $this->createLocale('en_US');

        $currency = $this->createCurrency($currencyCode);

        $channel = $this->createChannel('WEB', 'Web Channel', $defaultLocale, $currency);

        $zone = $this->createCountryAndZone();
        $this->createShippingMethod($channel, $zone);

        $this->entityManager->flush();
    }

    private function createLocale(string $code): LocaleInterface
    {
        /** @var LocaleInterface $locale */
        $locale = $this->localeFactory->createNew();
        $locale->setCode($code);

        $this->localeRepository->add($locale);

        return $locale;
    }

    private function createCurrency(string $code): CurrencyInterface
    {
        /** @var CurrencyInterface $currency */
        $currency = $this->currencyFactory->createNew();
        $currency->setCode($code);

        $this->currencyRepository->add($currency);

        return $currency;
    }

    private function createChannel(
        string $code,
        string $name,
        LocaleInterface $defaultLocale,
        CurrencyInterface $baseCurrency
    ): ChannelInterface {
        /** @var ChannelInterface $channel */
        $channel = $this->channelFactory->createNew();
        $channel->setCode($code);
        $channel->setName($name);
        $channel->setDefaultLocale($defaultLocale);
        $channel->addLocale($defaultLocale);
        $channel->setBaseCurrency($baseCurrency);
        $channel->addCurrency($baseCurrency);
        $channel->setTaxCalculationStrategy('order_items_based');
        $channel->setHostname('localhost');
        $channel->setEnabled(true);

        $this->channelRepository->add($channel);

        return $channel;
    }

    private function createCountryAndZone(): ZoneInterface
    {
        /** @var CountryInterface $country */
        $country = $this->countryFactory->createNew();
        $country->setCode('US');
        $country->setEnabled(true);

        $this->countryRepository->add($country);

        /** @var \Sylius\Component\Addressing\Model\ZoneMemberInterface $zoneMember */
        $zoneMember = $this->zoneMemberFactory->createNew();
        $zoneMember->setCode('US');

        /** @var ZoneInterface $zone */
        $zone = $this->zoneFactory->createNew();
        $zone->setCode('US');
        $zone->setName('United States');
        $zone->setType('country');
        $zone->addMember($zoneMember);

        $this->zoneRepository->add($zone);

        return $zone;
    }

    private function createShippingMethod(ChannelInterface $channel, ZoneInterface $zone): void
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = $this->shippingMethodFactory->createNew();
        $shippingMethod->setCode('standard');
        $shippingMethod->setName('Standard Shipping');
        $shippingMethod->setEnabled(true);
        $shippingMethod->setZone($zone);
        $shippingMethod->setCalculator('flat_rate');
        $shippingMethod->setConfiguration([
            $channel->getCode() => [
                'amount' => 0,
            ],
        ]);
        $shippingMethod->addChannel($channel);

        $this->shippingMethodRepository->add($shippingMethod);
    }
}
