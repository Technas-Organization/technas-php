# Technas pour PHP

Les paiements et les comptes de vos utilisateurs derrière **un seul client et un seul secret**.
[Read in English](README.md).

```php
use Technas\Technas;

$t = new Technas();                             // lit TECHNAS_SECRET

$payment = $t->pay->payments->create([
    'amount' => 5000, 'reference' => 'commande-42',
    'customer' => ['email' => 'awa@exemple.com', 'name' => 'Awa Mensah'],
    'returnUrl' => 'https://boutique.exemple/merci',
]);
echo $payment->checkoutUrl;

$user = $t->accounts->users->resolve(phone: '+22890123456', proof: 'verified');
$check = $t->accounts->tokens->verify($jetonDAcces);
```

## Choisir son paquet

Comme une boîte à outils livrée en plusieurs variantes, Technas se prend à la carte. Le même
code, plusieurs découpes :

| Vous voulez | Installer |
|---|---|
| Tout | `technas/technas` (ce paquet) : `new Technas\Technas()` |
| Les paiements seuls | [`technas/pay`](https://github.com/Technas-Organization/technas-pay-php) |
| Les comptes seuls | [`technas/accounts`](https://github.com/Technas-Organization/technas-accounts-php) |

Le paquet complet dépend des paquets minces, jamais l'inverse : un paquet mince ne tire rien d'un
autre produit. Dans `technas/technas`, le client d'un produit n'est créé (et ses classes chargées) qu'au moment où vous y touchez.

## Installer

```bash
composer config repositories.technas vcs https://github.com/Technas-Organization/technas-php
composer config repositories.technas-pay vcs https://github.com/Technas-Organization/technas-pay-php
composer config repositories.technas-accounts vcs https://github.com/Technas-Organization/technas-accounts-php
composer require technas/technas:^0.1
```

PHP 8.1 ou plus. Composer ne lit `repositories` que dans VOTRE projet : tant que les paquets ne sont pas sur Packagist, les trois lignes ci-dessus sont nécessaires.

Votre secret vient de la [console](https://console.technas.fr) (**Développeurs › Clés**) :
`tps_test_…` pour le bac à sable (aucun argent réel, utilisateurs du bac à sable), `tps_live_…`
pour la production. Posez-le dans `TECHNAS_SECRET`.

## Ce qu'il y a dedans

| | |
|---|---|
| `$t->pay->payments` | `create`, `get`, `getByReference`, `cancel`, `refund`, `list` |
| `$t->pay->refunds` / `$t->pay->balance` | `get` / `get`, `topUp` |
| `$t->pay->webhooks` | `verify`, `handle` (webhooks signés) |
| `$t->pay->client` | Tout le client mince `technas/pay`, pour le reste (numéro de reversement, compte carte, règles de devise, routes toutes faites pour l'app…) |
| `$t->accounts->users` | `resolve`, `get`, `list`, `setRoles`, `disable`, `enable`, `revokeSessions` |
| `$t->accounts->roles` / `$t->accounts->tokens` | `list`, `declare` / `verify` |
| `$t->accounts->config()` | Ce que votre app branche pour connecter ses utilisateurs |

Chaque produit garde sa documentation : [paiements](https://github.com/Technas-Organization/technas-pay-php#readme),
[comptes](https://github.com/Technas-Organization/technas-accounts-php/blob/main/README.fr.md). La référence complète de l'API est dans
la console (**Développeurs › Référence**).

## Tests

```bash
composer test
```

Licence MIT.
