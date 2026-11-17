<?php

declare(strict_types=1);

namespace Technas\Pay;

use TechnasPay\Model\TopUp;
use TechnasPay\TechnasPay;

final class Balance
{
    /** @internal */
    public function __construct(private readonly TechnasPay $client)
    {
    }

    /** @return array<int, mixed> */
    public function get(): array
    {
        return $this->client->getBalance();
    }

    public function topUp(int $amount, string $currency, ?string $returnUrl = null): TopUp
    {
        return $this->client->topUpBalance($amount, $currency, $returnUrl);
    }
}
