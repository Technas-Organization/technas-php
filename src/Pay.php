<?php

declare(strict_types=1);

namespace Technas;

use TechnasPay\TechnasPay;

/**
 * `$t->pay`: the thin technas/pay client, grouped by resource. Nothing is reimplemented:
 * `payments->create` IS `askForPayment`. Everything else stays reachable as `$t->pay->client`.
 */
final class Pay
{
    public readonly TechnasPay $client;
    public readonly Pay\Payments $payments;
    public readonly Pay\Refunds $refunds;
    public readonly Pay\Balance $balance;
    public readonly Pay\Webhooks $webhooks;

    public function __construct(#[\SensitiveParameter] ?string $secret = null, ?string $baseUrl = null, ?object $transport = null)
    {
        $this->client = new TechnasPay($secret, $baseUrl ?? TechnasPay::DEFAULT_BASE_URL, $transport);
        $this->payments = new Pay\Payments($this->client);
        $this->refunds = new Pay\Refunds($this->client);
        $this->balance = new Pay\Balance($this->client);
        $this->webhooks = new Pay\Webhooks($this->client);
    }

    public function isTestMode(): bool
    {
        return $this->client->isTestMode();
    }
}
