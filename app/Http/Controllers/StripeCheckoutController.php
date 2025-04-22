<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use App\Models\Reservation;

class StripeCheckoutController extends Controller {

    public function checkout(Request $request) {
        //dd($request->all());
        // Add debugging to see what's happening
        \Log::info('Checkout initiated for reservation: ' . $request->reservation_id);

        $reservation = Reservation::with('room')->findOrFail($request->reservation_id);

        // Ensure the price is properly formatted - make sure it's a positive integer
        $amount = max((int)$reservation->price, 100); // Minimum 1 USD (100 cents)

        \Log::info('Amount to charge: ' . $amount);

        // Make sure Stripe API key is set
        $stripeKey = config('services.stripe.secret');
        if (empty($stripeKey)) {
            \Log::error('Stripe secret key is missing');
            return redirect()->route('client.reservations')->with('error', 'Payment configuration error');
        }

        Stripe::setApiKey($stripeKey);

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $amount,
                        'product_data' => [
                            'name' => 'Reservation #' . $reservation->id,
                            'description' => 'Room: ' . $reservation->room->name,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}&reservation_id=' . $reservation->id,
                'cancel_url' => route('client.reservations'),
            ]);

            \Log::info('Stripe session created: ' . $session->id);

            // Redirect to Stripe checkout page
            return redirect()->away($session->url);
        } catch (\Exception $e) {
            \Log::error('Stripe error: ' . $e->getMessage());
            return redirect()->route('client.reservations')->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function success(Request $request) {
        \Log::info('Payment success callback received', $request->all());

        // Mark the reservation as paid if needed
        if ($request->has('reservation_id')) {
            $reservation = Reservation::find($request->reservation_id);
            if ($reservation) {
                $reservation->payment_id = $request->session_id;
                $reservation->save();
                \Log::info('Reservation marked as paid: ' . $reservation->id);
            }
        }

        return Inertia::render('SuccessPayment');
    }
}
