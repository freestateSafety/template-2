<?php
namespace App\Service;

use App\Entity\Address;
use BigE\SimpleUps\BaseAddressEnum;
use BigE\SimpleUps\Entity\PackageWeight;
use BigE\SimpleUps\Entity\Rate;
use BigE\SimpleUps\Entity\Security\Token;
use BigE\SimpleUps\Entity\ServiceEnum;
use BigE\SimpleUps\Entity\ShipTo;
use BigE\SimpleUps\Entity\Shipper;
use BigE\SimpleUps\Entity\UnitOfMeasurement\Weight;
use BigE\SimpleUps\Entity\UnitOfMeasurement\WeightEnum;
use BigE\SimpleUps\Rating;
use BigE\SimpleUps\Security;
use BigE\SimpleUps\VersionEnum;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class UPSService
{
    private Rating $rating;

    private Security $security;

    private Token $token;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private LoggerInterface $logger,
        private ClientInterface $client = new Client(),
        private HttpFactory $factory = new HttpFactory()
    )
    {
        $this->parameterBag->has('ups.shipper.number') || throw new \ValueError('ups.shipper.number is required');
    }

    /**
     * @param Address $address
     * @param float $weight
     * @return array
     */
    public function getRates(Address $address, float $weight): array
    {
        if (empty($this->rating)) {
            $this->rating = new Rating($this->client, $this->factory, $this->factory, BaseAddressEnum::Production);
            $this->rating->setLogger($this->logger);
        }

        if (empty($this->token) || $this->token->isExpired())
            $this->_createSecurityToken();

        $this->rating->setToken($this->token);
        $this->logger->debug('Preparing rate request for UPS');
        $shipper = new Shipper(
            new Shipper\Address(
                [
                    $this->parameterBag->get('ups.shipper.street'),
                ],
                $this->parameterBag->get('ups.shipper.country'),
                $this->parameterBag->get('ups.shipper.city'),
                $this->parameterBag->get('ups.shipper.state'),
                $this->parameterBag->get('ups.shipper.zip')
            ),
            $this->parameterBag->get('ups.shipper.addressee')
        );

        $shipTo = new ShipTo(
            new ShipTo\Address(
                [
                    $address->getAddress1(),
                    $address->getAddress2()
                ],
                $this->parameterBag->get('ups.shipper.country'),
                $address->getCity(),
                $address->getState(),
                $address->getZip()
            ),
            $address->getCustomer()->getName()
        );

        $response = $this->rating->rate(new Rate(
            new Rate\RateRequest(
                new Rate\RateRequest\Request(Rate\RateRequest\RequestOptionEnum::Shop),
                new Rate\RateRequest\Shipment(
                    [
                        new Rate\RateRequest\Shipment\Package(
                            new Rate\RateRequest\Shipment\Package\PackagingType(Rate\RateRequest\Shipment\Package\PackagingTypeEnum::CustomerPackage),
                            new PackageWeight(new Weight(WeightEnum::Pounds, WeightEnum::Pounds->name), (string) $weight)
                        )
                    ],
                    $shipper,
                    $shipTo,
                    null,
                    new Rate\RateRequest\Shipment\Service(
                        ServiceEnum::UPSGround
                    )
                )
            )
        ), VersionEnum::v1, Rating\Rate\RequestOptionEnum::Shop);

        $this->logger->debug('Received response from UPS');
        return (!is_array($response->RatedShipment))? [$response->RatedShipment, ] : $response->RatedShipment;
    }

    private function _createSecurityToken()
    {
        if (empty($this->security)) {
            $this->security = new Security($this->client, $this->factory, $this->factory, BaseAddressEnum::Production);
            $this->security->setLogger($this->logger);
        }

        $this->token = $this->security->createToken($this->parameterBag->get('ups.client.id'), $this->parameterBag->get('ups.client.secret'));
    }
}
