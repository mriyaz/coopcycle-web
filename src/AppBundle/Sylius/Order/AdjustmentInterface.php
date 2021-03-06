<?php

namespace AppBundle\Sylius\Order;

use Sylius\Component\Order\Model\AdjustmentInterface as BaseAdjustmentInterface;

interface AdjustmentInterface extends BaseAdjustmentInterface
{
    public const TAX_ADJUSTMENT = 'tax';
    public const DELIVERY_ADJUSTMENT = 'delivery';
    public const MENU_ITEM_MODIFIER_ADJUSTMENT = 'menu_item_modifier';
    public const FEE_ADJUSTMENT = 'fee';
}
