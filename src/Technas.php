<?php

declare(strict_types=1);

namespace Technas;

use Technas\Accounts\TechnasAccounts;

/**
 * Technas: every Technas product behind one client and one secret.
 *
 *     $t = new Technas();                                       // reads TECHNAS_SECRET
 *     $payment = $t->pay->payments->create([...]);
 *     $user = $t->accounts->users->resolve(phone: '+22890123456', proof: 'verified');
 *
 * The FULL package: it requires the thin ones (technas/pay, technas/accounts) and adds only this
 * entry point. A product client is created, and its classes loaded, the first time you touch it.
 *
 * @property-read Pay $pay Payments: mobile money and card.
 * @property-read TechnasAccounts $accounts Your app's users.
 */
final class Technas
{
    /** The secret lives in a closure: neither var_dump, nor a cast, nor a stack trace can print it. */
    private readonly \Closure $secretSource;
    private ?Pay $payClient = null;
    private ?TechnasAccounts $accountsClient = null;

    /**
     * @param \TechnasPay\Http\Transport|null $payTransport Your own transport for payments (tests, proxy).
     * @param Accounts\Transport|null $accountsTransport Your own transport for accounts (tests, proxy).
     */
    public function __construct(
        #[\SensitiveParameter] ?string $secret = null,
        private readonly ?string $baseUrl = null,
        private readonly ?object $payTransport = null,
        private readonly ?Accounts\Transport $accountsTransport = null,
    ) {
        $this->secretSource = static function () use ($secret): ?string {
            if ($secret !== null) {
                return $secret;
            }
            foreach (['TECHNAS_SECRET', 'TECHNAS_PAY_SECRET'] as $name) {
                $value = getenv($name);
                if (is_string($value) && $value !== '') {
                    return $value;
                }
            }
            return null;
        };
    }

    public function __get(string $name): Pay|TechnasAccounts
    {
        return match ($name) {
            'pay' => $this->payClient ??= new Pay(($this->secretSource)(), $this->baseUrl, $this->payTransport),
            'accounts' => $this->accountsClient ??= new TechnasAccounts(
                ($this->secretSource)(),
                $this->baseUrl ?? TechnasAccounts::DEFAULT_BASE_URL,
                $this->accountsTransport,
            ),
            default => throw new \LogicException("Technas has no product \"$name\""),
        };
    }

    public function __isset(string $name): bool
    {
        return $name === 'pay' || $name === 'accounts';
    }

    /** The client carries the secret: it does not serialize (cache, session, queue). */
    public function __serialize(): array
    {
        throw new \LogicException('Technas cannot be serialized');
    }

    /** @return array<string, mixed> */
    public function __debugInfo(): array
    {
        return [];
    }
}
