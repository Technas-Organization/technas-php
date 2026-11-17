<?php

declare(strict_types=1);

namespace Technas\Pay;

use TechnasPay\TechnasPay;
use TechnasPay\Webhook\Event;
use TechnasPay\Webhook\WebhookHandlers;

final class Webhooks
{
    /** @internal */
    public function __construct(private readonly TechnasPay $client)
    {
    }

    /** The event of a signed webhook, or an exception. */
    public function verify(string $rawBody, ?string $signatureHeader): Event
    {
        return $this->client->verifyWebhook($rawBody, $signatureHeader);
    }

    /** @return array<string, mixed> */
    public function handle(string $rawBody, ?string $signatureHeader, WebhookHandlers $handlers): array
    {
        return $this->client->handleWebhook($rawBody, $signatureHeader, $handlers);
    }
}
