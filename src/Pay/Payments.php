<?php

declare(strict_types=1);

namespace Technas\Pay;

use TechnasPay\Model\Payment;
use TechnasPay\Model\Refund;
use TechnasPay\TechnasPay;

final class Payments
{
    /** @internal */
    public function __construct(private readonly TechnasPay $client)
    {
    }

    /**
     * Ask a customer for a payment. Send them to the returned `checkoutUrl`.
     *
     * @param array<string, mixed> $input amount, reference, customer, returnUrl, and the options of technas/pay
     */
    public function create(array $input): Payment
    {
        return $this->client->askForPayment($input);
    }

    public function get(string $id): Payment
    {
        return $this->client->getPayment($id);
    }

    public function getByReference(string $reference): Payment
    {
        return $this->client->getPaymentByReference($reference);
    }

    public function cancel(string $id): Payment
    {
        return $this->client->cancelPayment($id);
    }

    public function refund(string $id, int|float|null $amount = null, string $fees = 'partner', ?string $reference = null): Refund
    {
        return $this->client->refund($id, $amount, $fees, $reference);
    }

    /** @return array<string, mixed> */
    public function list(?int $limit = null, ?string $status = null, ?string $cursor = null): array
    {
        return $this->client->listTransactions($limit, $status, $cursor);
    }
}
