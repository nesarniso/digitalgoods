<?php

namespace App\Domain\Shipping\Contracts;

interface CourierGatewayInterface extends CourierInterface
{
    // Concrete courier implementations may opt into gateway-specific
    // capabilities later; the common contract remains the shared phase
    // contract used by ShipmentService and any admin workflow.
}
