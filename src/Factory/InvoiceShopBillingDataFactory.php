<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\InvoicingPlugin\Factory;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopBillingDataInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceShopBillingDataInterface;
use Webmozart\Assert\Assert;

final class InvoiceShopBillingDataFactory implements InvoiceShopBillingDataFactoryInterface
{
    /**
     * @var string
     * @psalm-var class-string
     */
    private $className;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function createNew(): InvoiceShopBillingDataInterface
    {
        /** @var InvoiceShopBillingDataInterface $invoiceShopBillingData */
        $invoiceShopBillingData = new $this->className();

        Assert::isInstanceOf($invoiceShopBillingData, InvoiceShopBillingDataInterface::class);

        return $invoiceShopBillingData;
    }

    public function createFromChannel(ChannelInterface $channel): InvoiceShopBillingDataInterface
    {
        $shopBillingData = $channel->getShopBillingData();

        if (null === $shopBillingData) {
            return $this->createNew();
        }

        return $this->createFromShopBillingData($shopBillingData);
    }

    public function createFromShopBillingData(ShopBillingDataInterface $shopBillingData): InvoiceShopBillingDataInterface
    {
        $invoiceShopBillingData = $this->createNew();

        $invoiceShopBillingData->setCompany($shopBillingData->getCompany());
        $invoiceShopBillingData->setTaxId($shopBillingData->getTaxId());
        $invoiceShopBillingData->setCountryCode($shopBillingData->getCountryCode());
        $invoiceShopBillingData->setStreet($shopBillingData->getStreet());
        $invoiceShopBillingData->setCity($shopBillingData->getCity());
        $invoiceShopBillingData->setPostcode($shopBillingData->getPostcode());

        return $invoiceShopBillingData;
    }
}
