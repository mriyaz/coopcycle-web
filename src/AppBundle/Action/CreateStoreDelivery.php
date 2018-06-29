<?php

namespace AppBundle\Action;

use AppBundle\Entity\Base\GeoCoordinates;
use AppBundle\Entity\Address;
use AppBundle\Entity\Delivery;
use AppBundle\Entity\Store;
use AppBundle\Service\DeliveryManager;
use AppBundle\Service\SettingsManager;
use AppBundle\Service\RoutingInterface;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle6\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CreateStoreDelivery
{
    private $geocoder;
    private $deliveryManager;
    private $routing;
    private $tokenStorage;
    private $serializer;

    public function __construct(
        SettingsManager $settingsManager,
        DeliveryManager $deliveryManager,
        RoutingInterface $routing,
        TokenStorageInterface $tokenStorage,
        $serializer,
        $doctrine,
        $locale)
    {
        $httpClient = new Client();
        $provider = new GoogleMaps($httpClient, null, $settingsManager->get('google_api_key'));

        $this->geocoder = new StatefulGeocoder($provider, $locale);
        $this->deliveryManager = $deliveryManager;
        $this->routing = $routing;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route(
     *     name="create_store_delivery",
     *     path="/stores/deliveries"
     * )
     * @Method("POST")
     */
    public function createStoreDeliveryAction(Request $request)
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            // TODO Throw Exception
            return;
        }

        if (null === $store = $token->getAttribute('store')) {
            // TODO Throw Exception
            return;
        }

        $content = $request->getContent();
        if (!empty($content)) {
            $data = json_decode($content, true);
        }

        $address = $data['dropoff']['address'];

        $results = $this->geocoder->geocodeQuery(GeocodeQuery::create($address));
        [ $longitude, $latitude ] = $results->first()->getCoordinates()->toArray();

        $pickupAddress = $store->getAddress();

        $dropoffAddress = new Address();
        $dropoffAddress->setGeo(new GeoCoordinates($latitude, $longitude));

        $data = $this->routing->getRawResponse(
            $pickupAddress->getGeo(),
            $dropoffAddress->getGeo()
        );

        $distance = $data['routes'][0]['distance'];

        $delivery = new Delivery();
        $delivery->getPickup()->setAddress($pickupAddress);
        $delivery->getDropoff()->setAddress($dropoffAddress);
        $delivery->setVehicle(Delivery::VEHICLE_BIKE);
        // $delivery->setWeight($request->query->get('weight', null));
        $delivery->setDistance($distance);

        $dropoffDoneBefore = new \DateTime();
        $dropoffDoneBefore->modify('+1 day');

        $pickupDoneBefore = clone $dropoffDoneBefore;
        $pickupDoneBefore->modify('-1 hour');

        $this->doctrine->getManagerForClass(Delivery::class)->persist($delivery);
        $this->doctrine->getManagerForClass(Delivery::class)->flush();

        $normalized = $this->serializer->normalize($delivery, 'json', [
            'resource_class' => Delivery::class,
            'operation_type' => 'item',
            'item_operation_name' => 'get'
        ]);

        return new JsonResponse($normalized);
    }
}
