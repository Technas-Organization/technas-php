<?php

// One secret, two products, each loaded only when used. No dev dependency: `php tests/run.php`.
// Locally the thin packages are read from sibling checkouts or from vendor/.

declare(strict_types=1);

$roots = [
    'Technas\\Accounts\\' => [__DIR__ . '/../vendor/technas/accounts/src/', __DIR__ . '/../../technas-accounts-php/src/'],
    'TechnasPay\\' => [__DIR__ . '/../vendor/technas/pay/src/', __DIR__ . '/../../technas-pay-php/src/'],
    'Technas\\' => [__DIR__ . '/../src/'],
];
$loaded = [];
spl_autoload_register(static function (string $class) use ($roots, &$loaded): void {
    foreach ($roots as $prefix => $dirs) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        foreach ($dirs as $dir) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                $loaded[] = $class;
                require $file;
                return;
            }
        }
    }
});

use Technas\Technas;

const SECRET = 'tps_test_pk_demo12345678.whsec_demo';
const UID = '7d3f2a10-1111-4222-8333-444455556666';
$count = 0;

function check(bool $ok, string $what): void
{
    global $count;
    $count++;
    if (!$ok) {
        fwrite(STDERR, "FAILED: $what\n");
        exit(1);
    }
}

$seen = [];
$answer = static function (string $method, string $url, array $headers, ?string $body) use (&$seen): array {
    $path = (string) parse_url($url, PHP_URL_PATH);
    $seen[] = ['path' => $path, 'auth' => $headers['Authorization'] ?? ''];
    if ($path === '/v1/payments') {
        $sent = json_decode((string) $body, true);
        return ['status' => 200, 'body' => json_encode(['id' => '9c1f0a10-2222-4333-8444-555566667777', 'status' => 'pending',
            'reference' => $sent['reference'], 'amount' => $sent['amount'], 'currency' => 'XOF', 'mode' => 'test',
            'checkout_url' => 'https://pay-api.technas.fr/test-checkout/x'])];
    }
    if ($path === '/v1/accounts/users/resolve') {
        return ['status' => 200, 'body' => json_encode(['id' => UID, 'created' => true, 'phone' => '+22890000002',
            'phone_verified' => true, 'email' => null, 'email_verified' => false])];
    }
    return ['status' => 404, 'body' => json_encode(['code' => 'not_found', 'message' => 'Not found'])];
};

$payTransport = new class ($answer) implements \TechnasPay\Http\Transport {
    public function __construct(private \Closure $answer)
    {
    }

    public function send(string $method, string $url, array $headers, ?string $body, bool $followRedirects = true): array
    {
        return ($this->answer)($method, $url, $headers, $body);
    }
};
$accountsTransport = new class ($answer) implements \Technas\Accounts\Transport {
    public function __construct(private \Closure $answer)
    {
    }

    public function send(string $method, string $url, array $headers, ?string $body): array
    {
        return ($this->answer)($method, $url, $headers, $body);
    }
};

// A product is only loaded when touched.
$loaded = [];
$t = new Technas(SECRET, payTransport: $payTransport, accountsTransport: $accountsTransport);
check(!in_array('TechnasPay\\TechnasPay', $loaded, true) && !in_array('Technas\\Accounts\\TechnasAccounts', $loaded, true), 'nothing loaded at construction');
$user = $t->accounts->users->resolve(phone: '+22890000002', proof: 'verified');
check(in_array('Technas\\Accounts\\TechnasAccounts', $loaded, true) && !in_array('TechnasPay\\TechnasPay', $loaded, true), 'accounts alone never loads the payment client');

// Pay and accounts share the secret and speak to their own routes.
$payment = $t->pay->payments->create(['amount' => 5000, 'reference' => 'order-42', 'currency' => 'XOF',
    'customer' => ['email' => 'awa@example.com', 'name' => 'Awa Mensah'], 'returnUrl' => 'https://shop.example/thanks']);
check($payment->reference === 'order-42' && $user['id'] === UID, 'both products answer');
check(array_column($seen, 'path') === ['/v1/accounts/users/resolve', '/v1/payments'], 'each product speaks to its own route');
check(array_unique(array_column($seen, 'auth')) === ['Bearer pk_demo12345678'], 'one secret for both');
check($t->pay === $t->pay && $t->pay->isTestMode() && $t->accounts->isTestMode(), 'a product is created once');
check(!str_contains(print_r($t, true), 'pk_demo12345678'), 'debug output hides the secret');

// The secret comes from TECHNAS_SECRET.
putenv('TECHNAS_SECRET=' . SECRET);
check((new Technas(accountsTransport: $accountsTransport))->accounts->isTestMode(), 'secret from the environment');

echo "OK ($count checks)\n";
