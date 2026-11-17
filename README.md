# Technas for PHP

Payments and user accounts behind **one client and one secret**. [Lire en français](README.fr.md).

```php
use Technas\Technas;

$t = new Technas();                             // reads TECHNAS_SECRET

$payment = $t->pay->payments->create([
    'amount' => 5000, 'reference' => 'order-42',
    'customer' => ['email' => 'awa@example.com', 'name' => 'Awa Mensah'],
    'returnUrl' => 'https://shop.example/thanks',
]);
echo $payment->checkoutUrl;

$user = $t->accounts->users->resolve(phone: '+22890123456', proof: 'verified');
$check = $t->accounts->tokens->verify($accessToken);
```

## Pick your package

Like a toolkit that ships in several builds, Technas comes in variants. Same code, different cuts:

| You want | Install |
|---|---|
| Everything | `technas/technas` (this package): `new Technas\Technas()` |
| Payments only | [`technas/pay`](https://github.com/Technas-Organization/technas-pay-php) |
| User accounts only | [`technas/accounts`](https://github.com/Technas-Organization/technas-accounts-php) |

The full package depends on the thin ones, never the reverse: a thin package pulls in nothing from
another product. Inside `technas/technas`, a product client is created (and its classes loaded) the first time you touch it.

## Install

```bash
composer config repositories.technas vcs https://github.com/Technas-Organization/technas-php
composer config repositories.technas-pay vcs https://github.com/Technas-Organization/technas-pay-php
composer config repositories.technas-accounts vcs https://github.com/Technas-Organization/technas-accounts-php
composer require technas/technas:^0.1
```

PHP 8.1+. Composer only reads `repositories` from YOUR project: until the packages are on Packagist, the three lines above are needed.

Your secret comes from the [console](https://console.technas.fr) (**Developers › Keys**):
`tps_test_…` for the sandbox (no real money, sandbox users), `tps_live_…` for production. Put it
in `TECHNAS_SECRET`.

## What is inside

| | |
|---|---|
| `$t->pay->payments` | `create`, `get`, `getByReference`, `cancel`, `refund`, `list` |
| `$t->pay->refunds` / `$t->pay->balance` | `get` / `get`, `topUp` |
| `$t->pay->webhooks` | `verify`, `handle` (signed webhooks) |
| `$t->pay->client` | The whole thin `technas/pay` client, for everything else (payout destination, card account, currency rules, ready-made app routes…) |
| `$t->accounts->users` | `resolve`, `get`, `list`, `setRoles`, `disable`, `enable`, `revokeSessions` |
| `$t->accounts->roles` / `$t->accounts->tokens` | `list`, `declare` / `verify` |
| `$t->accounts->config()` | What your app plugs in to sign users in |

Each product keeps its own documentation: [payments](https://github.com/Technas-Organization/technas-pay-php#readme),
[accounts](https://github.com/Technas-Organization/technas-accounts-php#readme). The full API reference lives in the console
(**Developers › Reference**).

## Tests

```bash
composer test
```

MIT licence.
