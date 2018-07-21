<?php

namespace AppBundle\Serializer;

use ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer;
use AppBundle\Entity\Address;
use AppBundle\Entity\Delivery;
use AppBundle\Entity\Task;
use AppBundle\Service\Geocoder;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DeliveryNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private $normalizer;
    private $geocoder;
    private $tokenStorage;

    public function __construct(
        ItemNormalizer $normalizer,
        Geocoder $geocoder,
        TokenStorageInterface $tokenStorage)
    {
        $this->normalizer = $normalizer;
        $this->geocoder = $geocoder;
        $this->tokenStorage = $tokenStorage;
    }

    private function normalizeTask(Task $task)
    {
        $address = $this->normalizer->normalize($task->getAddress(), 'jsonld', [
            'resource_class' => Address::class,
            'operation_type' => 'item',
            'item_operation_name' => 'get',
            'groups' => ['place']
        ]);

        return [
            'address' => $address
        ];
    }

    public function normalize($object, $format = null, array $context = array())
    {
        $data =  $this->normalizer->normalize($object, $format, $context);

        if (isset($data['items'])) {
            unset($data['items']);
        }

        $data['pickup'] = $this->normalizeTask($object->getPickup());
        $data['dropoff'] = $this->normalizeTask($object->getDropoff());

        $data['color'] = $object->getColor();

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format) && $data instanceof Delivery;
    }

    private function denormalizeTask($data, Task $task)
    {
        if (isset($data['doneBefore'])) {
            $task->setDoneBefore(new \DateTime($data['doneBefore']));
        }

        if (isset($data['address'])) {
            $address = $this->geocoder->geocode($data['address']);
            $task->setAddress($address);
        }
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $delivery = $this->normalizer->denormalize($data, $class, $format, $context);

        $pickup = $delivery->getPickup();
        $dropoff = $delivery->getDropoff();

        if (isset($data['pickup'])) {

            $this->denormalizeTask($data['pickup'], $pickup);

            // If no pickup address is specified, use the store address
            if (null === $pickup->getAddress()) {
                if (null === $token = $this->tokenStorage->getToken()) {
                    // TODO Throw Exception
                }
                if (null === $store = $token->getAttribute('store')) {
                    // TODO Throw Exception
                }
                $pickup->setAddress($store->getAddress());
            }
        }

        if (isset($data['dropoff'])) {
            $this->denormalizeTask($data['dropoff'], $dropoff);
        }

        return $delivery;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->normalizer->supportsDenormalization($data, $type, $format) && $type === Delivery::class;
    }
}
