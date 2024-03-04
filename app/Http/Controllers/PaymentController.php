<?php

namespace App\Http\Controllers;

use App\DataTables\SubscriptionDataTable;
use App\Models\Gender;
use App\Models\PaymentType;
use App\Models\States;
use App\Models\Subscription;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\CardException;
use Yajra\DataTables\Facades\DataTables;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index2(SubscriptionDataTable $request)
    // {
    //     return $request->render('page.payment-list.index');
    // }

    public function index2(Request $request)
    {
        $paymentTypeOptions = PaymentType::pluck('name')->toArray();
        array_unshift($paymentTypeOptions, '');

        $user = User::with(['roles'])->find(auth()->id());
        $subscribers = Subscription::wherecreatedBy($user->id)->get();
        $data = [];

        if (!is_null($subscribers)) {
            foreach ($subscribers as $subscriber) {
                Stripe::setApiKey(env('STRIPE_SECRET'));
                $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
                $get_payement_intent = $stripe->paymentIntents->retrieve(
                    $subscriber->payment_intent_id,
                    []
                );
                $res = json_encode($get_payement_intent, JSON_PRETTY_PRINT);
                $intentStatusMap = [
                    'requires_payment_method' => 'Payment requires a valid payment method',
                    'requires_confirmation' => 'Payment requires confirmation.',
                    'requires_action' => 'Payment requires additional action from the customer.',
                    'processing' => 'Payment is being processed.',
                    'succeeded' => 'Payment has succeeded.',
                    'requires_capture' => 'Payment is authorized and requires capture.',
                    'canceled' => 'Payment has been canceled.',
                    'abandoned' => 'Payment has been abandoned.',
                    'failed' => 'Payment has failed.'
                ];
                $item = json_decode($res, true);
                $data[] = [
                    "id" => $item['id'],
                    "name" => $item['metadata']['name'] ?: ($item['metadata']['event_name'] ?: (!empty($item['description']) ? $item['description'] : "N/A")),
                    "email" => isset($item['metadata']['email']) ? $item['metadata']['email'] : (isset($item['metadata']['purchaser_email']) ? $item['metadata']['purchaser_email'] : "N/A"),
                    "member_type" => $item['metadata']['level'] ?: "N/A",
                    "amount" => "$" . number_format($item['amount'] / 100, 2),
                    "created" => date("Y-m-d h:i A", $item['created']),
                    "status" => $item['status'] == "succeeded" ? "<span class='btn btn-square btn-success'>Payment Succeeded</span>" : "<span class='btn btn-square btn-danger' title='" . $intentStatusMap[$item['status']] . "'>Incomplete</span>",
                ];
            }
        }

        return view("page.payment-list.index", compact('data', 'paymentTypeOptions'));
    }

    // public function index2(Request $request){
    //     if($request->ajax()){

    //         return response()->json($subscriptions);
    //     }
    //     return view("page.payment-list.index");
    // }

    public function index(Request $request)
    {
        $paymentTypeOptions = PaymentType::pluck('name')->toArray();
        array_unshift($paymentTypeOptions, '');

        $user = User::with(['roles'])->find(auth()->id());
        $userRoles = $user->roles;
        $isAdmin = false;
        foreach ($userRoles as $role) {
            if ($role?->name == "Admin") {
                $isAdmin = true;
                break;
            }
        }
        if (!$isAdmin) {
            return redirect()->route("payment.list.user");
        }
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
        $data = $stripe->paymentIntents->all(['limit' => 100]);
        $res = $data->data;

        $data = [];

        $intentStatusMap = [
            'requires_payment_method' => 'Payment requires a valid payment method.',
            'requires_confirmation' => 'Payment requires confirmation.',
            'requires_action' => 'Payment requires additional action from the customer.',
            'processing' => 'Payment is being processed.',
            'succeeded' => 'Payment has succeeded.',
            'requires_capture' => 'Payment is authorized and requires capture.',
            'canceled' => 'Payment has been canceled.',
            'abandoned' => 'Payment has been abandoned.',
            'failed' => 'Payment has failed.'
        ];

        collect($res)->each(function ($item) use (&$data, $intentStatusMap) {
            $keys = array_keys((array)$item->metadata);
            $pattern = '/^product_\d+$/';
            $result = preg_grep($pattern, $keys);
            $key = false;
            if (!empty($result)) {
                $key = $item->metadata[current($result)];
            }
            $data[] = [
                "id" => $item->id,
                "name" => $item->metadata['name'] ?: ($item->metadata['event_name'] ?: ($key ?: (!empty($item->description) ? $item->description : "N/A"))),
                "email" => $item->metadata['email'] ?: ($item->metadata['purchaser_email'] ?: "N/A"),
                "member_type" => $item->metadata['level'] ?: "N/A",
                "amount" => "$" . number_format($item->amount / 100, 2),
                "created" => date("Y-m-d h:i A", $item->created),
                "status" => $item->status == "succeeded" ? "Payment Succeeded" : "Payment Incomplete",
            ];
        });
        return view("page.payment-list.index", compact('data', 'paymentTypeOptions'));
    }

    public static function registerCharges(array $user, $level)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
            if ($user['journal_preferred_delivery'] == "post") {
                $amount = 85;
            } else {
                $amount = 75;
            }
            // search for client in stripe api
            $customer = $stripe->customers->search([
                'query' => "email:'" . $user['email'] . "' AND username:'" . $user['username'] . "'",
            ]);
            if (empty($customer)) {
                // create client if it not exists
                $stripe->customers->create([
                    'description' => $level . ' level customer.',
                    'name' => $user['username'],
                    'email' => $user['email']
                ]);
            }
            // store card details to verify card

            // create payment intent and confirm payment
            $res = $stripe->paymentIntents->create([
                'amount' => $amount * 100,
                'currency' => 'AUD',
                'automatic_payment_methods' => [
                    'enabled' => true,
                    "allow_redirects" => "never"
                ],
                "description" => "Registration charges for user: " . $user['username']
            ]);
            $intent = PaymentIntent::retrieve($res->id);
            $intent->confirm([
                'payment_method' => "pm_card_visa",
            ]);
            return true;
        } catch (Exception $e) {
            abort(500);
        }
    }

    public static function createPaymentIntent(array $user, $level)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
            $amount = $user['preferred_delivery_price'];

            // search for client in stripe api
            // $customer = $stripe->customers->search([
            //     'query' => "email:'".$user['email']."' AND username:'".$user['username']."'",
            // ]);
            $customer = [];
            if (empty($customer)) {
                // create client if it not exists
                $customer = $stripe->customers->create([
                    'description' => $level . ' Member.',
                    'name' => $user['username'],
                    'email' => $user['email'],
                    "address" => [
                        "city" => $user['suburb'],
                        "country" => "AU",
                        "line1" => $user['number_street'],
                        "line2" => "",
                        "postal_code" => $user['post_code'],
                        "state" => States::find($user['state'])->code
                    ]
                ]);
            }

            // create payment intent
            $res = $stripe->paymentIntents->create([
                'amount' => $amount * 100,
                'currency' => 'AUD',
                'automatic_payment_methods' => [
                    'enabled' => true,
                    "allow_redirects" => "never"
                ],
                "description" => "Registration charges for user: " . $user['username'],
                "customer" => $customer->id,
                "metadata" => [
                    'name' => $user['username'],
                    'email' => $user['email'],
                    'level' => $level,
                    // "order_id" => $order_id
                ]
            ]);
            return $res?->client_secret;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function createPaymentIntentFriend(array $user, $level)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
            $amount = $user['preferred_delivery_price'];

            $address = $user['address']['address'];
            $state = States::where("name", $address['state'])->value("code");
            $customerDetails = [
                'description' => $level . ' Member.',
                'name' => $user['given_name'],
                "address" => [
                    "city" => $address['city'],
                    "country" => $address['country'],
                    "line1" => $address['line1'],
                    "line2" => $address['line2'],
                    "postal_code" => $address['postal_code'],
                    "state" => $state
                ]
            ];
            $customer = $stripe->customers->create($customerDetails);

            // create payment intent
            $res = $stripe->paymentIntents->create([
                'amount' => $amount * 100,
                'currency' => 'aud',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                "description" => "Registration charges for user: " . $user['given_name'],
                "customer" => $customer->id,
                "metadata" => [
                    'name' => $user['given_name'],
                    "member_id" => auth()->id() ?? "N/A",
                    "dob" => $user['date_of_birth'],
                    "gender" => Gender::find($user['gender'])->value("name"),
                    'level' => $level,
                    // "order_id" => $order_id
                ]
            ]);
            Subscription::create([
                "payment_intent_id" => $res?->id,
                "amount" => $amount,
                "created_by" => auth()->id() ?? 0,
                "member_type" => "JUNIOR",
                "payment_method" => "CARD",
                "status" => "PENDING",
                'meta_description' => $customerDetails
            ]);
            return $res?->client_secret;
        } catch (Exception $e) {
            dd($e->getMessage());
            return false;
        }
    }

    public static function createPaymentIntentJunior(array $user, $level)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
            $amount = $user['preferred_delivery_price'];

            // search for client in stripe api
            // $customer = $stripe->customers->search([
            //     'query' => "email:'".$user['email']."' AND username:'".$user['username']."'",
            // ]);
            $address = $user['address']['address'];
            $state = States::where("name", $address['state'])->value("code");
            $customerDetails = [
                'description' => $level . ' Member.',
                'name' => $user['given_name'],
                "address" => [
                    "city" => $address['city'],
                    "country" => $address['country'],
                    "line1" => $address['line1'],
                    "line2" => $address['line2'],
                    "postal_code" => $address['postal_code'],
                    "state" => $state
                ]
            ];
            $customer = $stripe->customers->create($customerDetails);

            // create payment intent
            $res = $stripe->paymentIntents->create([
                'amount' => $amount * 100,
                'currency' => 'aud',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                "description" => "Registration charges for user: " . $user['given_name'],
                "customer" => $customer->id,
                "metadata" => [
                    'name' => $user['given_name'],
                    "member_id" => auth()->id(),
                    "dob" => $user['date_of_birth'],
                    "gender" => Gender::find($user['gender'])->value("name"),
                    'level' => $level,
                    // "order_id" => $order_id
                ]
            ]);
            Subscription::create([
                "payment_intent_id" => $res?->id,
                "amount" => $amount,
                "created_by" => auth()->id(),
                "member_type" => "JUNIOR",
                "payment_method" => "CARD",
                "status" => "PENDING",
                'meta_description' => $customerDetails
            ]);
            return $res?->client_secret;
        } catch (Exception $e) {
            dd($e->getMessage());
            return false;
        }
    }

    public static function createPaymentIntentPrimary(array $user, $level = "PRIMARY")
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
            $amount = $user['preferred_delivery_price'];
            $address = $user['address']['address'];
            $state = States::where("name", $address['state'])->value("code");
            $customerDetails = [
                'description' => $level . ' Member.',
                'name' => $user['username'],
                'email' => $user['email'],
                "address" => [
                    "city" => $user['suburb'],
                    "country" => "AU",
                    "line1" => $user['number_street'],
                    "line2" => "",
                    "postal_code" => $user['post_code'],
                    "state" => States::find($user['state'])->code
                ]
            ];
            $customer = $stripe->customers->create($customerDetails);

            // create payment intent
            $res = $stripe->paymentIntents->create([
                'amount' => $amount * 100,
                'currency' => 'aud',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                "description" => "Registration charges for user: " . $user['given_name'],
                "customer" => $customer->id,
                "metadata" => [
                    'name' => $user['given_name'],
                    "member_id" => auth()->id() ?? "N/A",
                    "dob" => $user['date_of_birth'],
                    "gender" => Gender::find($user['gender'])?->value("name"),
                    'level' => $level,
                    "email" => $user['email'],
                    // "order_id" => $order_id
                ]
            ]);
            Subscription::create([
                "payment_intent_id" => $res?->id,
                "amount" => $amount,
                "created_by" => auth()->id() ?? 0,
                "member_type" => "PRIMARY",
                "payment_method" => "CARD",
                "status" => "PENDING",
                'meta_description' => $customerDetails,
            ]);
            return $res?->client_secret;
        } catch (Exception $e) {
            return false;
        }
    }

    function confirmPaymentIntent(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $payment_intent_id = $request->intent['payment_intent']['id'];
        $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));
        $payment = $stripe->paymentIntents->retrieve(
            $payment_intent_id,
            []
        );
        abort(503);
    }
}
