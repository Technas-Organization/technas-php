<?php

declare(strict_types=1);

namespace Technas\Pay;

use TechnasPay\Model\Refund;
use TechnasPay\TechnasPay;

final class Refunds
{
    /** @internal */
    public function __construct(private readonly TechnasPay $client)
    {
    }

    public function get(string $id): Refund
    {
        return $this->client->getRefund($id);
    }
}
