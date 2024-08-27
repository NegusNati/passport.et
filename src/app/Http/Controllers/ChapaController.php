<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Chapa\Chapa\Facades\Chapa as Chapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ChapaController extends Controller
{
    /**
     * Initialize Rave payment process
     * @return void
     */
    protected $reference;

    public function __construct()
    {
        $this->reference = Chapa::generateReference();
    }
    public function initialize(Request $request)
    {
        //This generates a payment reference
        $reference = $this->reference;


        $request->validate([
            'amount' => 'required|numeric|min:10',
            'firstName' => 'required|alpha:ascii|max:255|min:3',
            'lastName' => 'required|alpha:ascii|max:255|min:3',
            'phoneNumber' => 'required|digits:10|numeric|starts_with:09,07',
            'plan' => 'required',
        ]);









        // dd($request->all());

        // Enter the details of the payment
        $data = [

            'amount' => $request->amount,
            'email' => 'natnaelbirhanu22@gmail.com',
            'tx_ref' => $reference,
            'currency' => "ETB",
            'callback_url' => route('callback', [$reference]),
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'phone_number' => $request->phoneNumber,
            "customization" => [
                "title" => 'Chapa test',
                "description" => "the test "
            ]
        ];



        //first put a stop at here and check your info (need to be same as your Chapa profile)
        // dd($data);
        $payment = Chapa::initializePayment($data);
        //now Check if the payment returns 'success' or 'fail'
        // dd($payment);

        if ($payment['status'] !== 'success') {
            // notify something went wrong
            dd($payment);
            return;
        }
        // dd($payment);


        //if payment is successful
        // return redirect($payment['data']['checkout_url']);
        return Inertia::location($payment['data']['checkout_url']);
    }

    /**
     * Obtain Rave callback information
     * @return void
     */
    public function callback($reference, Request $request)
    {
        $data = Chapa::verifyTransaction($reference);

        if ($data['status'] == 'success') {
            Log::info('in Subscription created for user');

            // Retrieve the user by ID if possible
            $user = Auth::user();
            Log::alert($user);

            if ($user !== null) {
                Log::info($user->first_name);
                $subscription = $user->subscription ?: new Subscription();
                $subscription->user_id = $user->id;
                $subscription->plan = $request->plan;
                $subscription->save();

                Log::info('Subscription created for user');
            } else {
                // If user is not authenticated, try to retrieve the user ID from the request data
                $userId = $data['user_id'] ?? null; // Adjust this line according to how the user ID is available in your data
                if ($userId) {
                    $user = User::findOrFail($userId);
                    Log::info($user);
                    if ($user) {
                        Auth::login($user);
                        Log::info('User authenticated in callback:', ['user_id' => $user->id, 'first_name' => $user->first_name]);

                        $subscription = $user->subscription ?: new Subscription();
                        $subscription->user_id = $user->id;
                        $subscription->plan = $request->plan;
                        $subscription->save();

                        Log::info('Subscription created for user');
                    } else {
                        Log::info('User not found');
                    }
                } else {
                    Log::info('User ID not available in data');
                }
            }

            return Inertia::location(route('dashboard'));
        } else {
            Log::info($data);
        }

        return Inertia::location(route('dashboard'));
    }
}
