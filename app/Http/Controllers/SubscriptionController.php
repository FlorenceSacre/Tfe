<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function __invoke() {
        request()->validate([
            'stripeToken' => ['required'],
            'amount' => ['required', Rule::in([5,12])],
            'street' => ['required'],
            'postcode' => ['required'],
            'city' => ['required'],
            'country' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $customer = \Stripe\Customer::create([
            'source' => request('stripeToken'),
            'name' => request('name'),
            'email' => request('email'),
        ]);

        $users = '';
        $email_form = request('email');
        $verif_email = DB::table('users')->where('email',$email_form)->first();
        if(!isset($verif_email)) {
            if (request('amount') == '5') {
                $role = 'subscriber';
            } else {
                $role = 'subunlimited';
            }
            $users = User::create([
                'name' => request('name'),
                'street' => request('street'),
                'postcode' => request('postcode'),
                'city' => request('city'),
                'email' => request('email'),
                'password' => request('password'),
                'role' => $role,
                'remember_token' => request('stripeToken'),
                'country_id' => request('country')
            ]);
        }

        \Stripe\Subscription::create([
            'customer' => $customer->id,
            'items' => [['plan' => request('amount')]],
        ]);

        if(!isset($verif_email)) {
            $idusers = $users->id;
        } else {
            $idusers = $verif_email->id;
        }
        $subscription = Subscriptions::create([
            'stripe_id' => $customer->id,
            'name' => request('name'),
            'email' => request('email'),
            'amount' => request('amount'),
            'unsubscription_token' => Str::random(),
            'users_id' => $idusers
        ]);

        $subscription->sendMeAnEmail();

        return view('subscribe.subscriptionOk');
    }
}