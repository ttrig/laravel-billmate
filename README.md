<p align="center">
<a href="https://github.com/ttrig/laravel-billmate/actions"><img src="https://github.com/ttrig/laravel-billmate/actions/workflows/build.yml/badge.svg" alt="Build Status"></a>
<a href="https://codecov.io/gh/ttrig/laravel-billmate"><img src="https://img.shields.io/codecov/c/github/ttrig/laravel-billmate/master.svg" alt="Codecov"></a>
<a href="https://packagist.org/packages/ttrig/laravel-billmate"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/ttrig/laravel-billmate"></a>
<a href="https://github.com/ttrig/laravel-billmate/blob/master/LICENSE.md"><img src="https://img.shields.io/github/license/ttrig/laravel-billmate.svg" alt="License"></a>
</p>

# Laravel Billmate

Laravel package for interacting with Billmate API.

## Installation

```shell
composer require ttrig/laravel-billmate
```

## Configuration

Publish the configuration file using this command:

```shell
php artisan vendor:publish --provider="Ttrig\Billmate\ServiceProvider"
```

Add a Billmate ID and key in `.env`.

```shell
BILLMATE_ID=123
BILLMATE_KEY=abc
BILLMATE_TEST=true
```

Update `config/billmate.php` to use your own controller(s).

```php
'accept_action' => 'App\Http\Controllers\BillmateController@accept',
'cancel_action' => 'App\Http\Controllers\BillmateController@cancel',
'callback_action' => \Ttrig\Billmate\Controllers\CallbackController::class,
```

## General payment flow

* https://developer.billmate.se/checkout-documentation
* https://developer.billmate.se/api-integration/getpaymentinfo
* https://developer.billmate.se/api-integration/initcheckout

## Usage example

### Checkout

```php
use Ttrig\Billmate\Article as BillmateArticle;
use Ttrig\Billmate\Service as BillmateService;

class CheckoutController extends Controller
{
    public function index(BillmateService $billmate)
    {
        $articles = collect()->push(new BillmateArticle([
            'title' => '1kg potatoes',
            'price' => 30,
        ]));

        $checkout = $billmate->initCheckout($articles);

        return view('checkout', compact('checkout'));
    }
}
```

You can view or update the data to be sent to Billmate by passing a callback
as second argument to `initCheckout`.

```php
$billmate->initCheckout($articles, function (&$data) {
    data_set($data, 'PaymentData.autoactivate', '1');
});
```

#### View

To render the Billmate Checkout iframe you can use `$checkout->iframe()` in
your blade template or write your own iframe and pass `$checkout->url` to its `src`
attribute.

#### JavaScript

To update height of the Checkout when it updates, we need this JavaScript.

```javascript
window.addEventListener('message', function (event) {
    if (event.origin !== 'https://checkout.billmate.se') {
        return
    }

    try {
        var json = JSON.parse(event.data)
    } catch (e) {
        return
    }

    if (json.event === 'content_height') {
        $('#checkout').height(json.data)
    }
})
```

#### Redirect controller

You need your own controller(s) for handling the accept and cancel redirections.

```php
use Ttrig\Billmate\Order as BillmateOrder;
use Ttrig\Billmate\Service as BillmateService;

class YourRedirectController extends Controller
{
    public function accept(BillmateService $billmate)
    {
        $order = new BillmateOrder(request()->data);

        $paymentInfo = $billmate->getPaymentInfo($order);

        return view('payment.accept');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}
```

#### Callback controller

If you use `Ttrig\Billmate\Controllers\CallbackController::class` as your
"callback_action" in `config/billmate.php`, you need to listen to the
`Ttrig\Billmate\Events\OrderCreated` event in your `EventServiceProvider`
to handle the order.

```php
protected $listen = [
    \Ttrig\Billmate\Events\OrderCreated::class => [
        \App\Listeners\DoSomething::class,
    ],
];
```

Read more about events at https://laravel.com/docs/9.x/events.

## Contributing

Contributions are what make the open source community such an amazing place to
be learn, inspire, and create.
Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b amazing-feature`)
3. Commit your Changes (`git commit -m 'Add some amazing feature`)
4. Push to the Branch (`git push origin amazing-feature`)
5. Open a Pull Request

## License

laravel-billmate is open-sourced software licensed under the [MIT license](./LICENSE.md).
