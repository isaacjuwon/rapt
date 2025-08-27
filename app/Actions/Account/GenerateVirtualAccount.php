<?php

namespace App\Actions\Account;

use App\Managers\ApiManager;
use App\Models\User;
use App\Models\Account;
use App\Integrations\Paystack\Entities\CreateDedicatedVirtualAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateVirtualAccount
{
    protected ApiManager $apiManager;

    public function __construct(ApiManager $apiManager)
    {
        $this->apiManager = $apiManager;
    }

    public function execute(User $user): ?Account
    {
        DB::beginTransaction();
        try {
            // Prepare data for Paystack API
                        $customerCode = $user->email; // Use user's email as customer code for Paystack API

            $virtualAccountEntity = CreateDedicatedVirtualAccount::withCustomerDetails(
                customer: $customerCode,
                firstName: $user->first_name,
                lastName: $user->last_name,
                phone: $user->phone_number ?? null // Assuming user has a phone_number field
            );

            // Call Paystack API to create dedicated virtual account
            $response = $this->apiManager->createDedicatedVirtualAccount($virtualAccountEntity);

            if (isset($response['status']) && $response['status'] === true) {
                $data = $response['data'];
                $accountNumber = $data['account_number'];
                $bankName = $data['bank']['bank_name'];
                $bankId = $data['bank']['id'];
                $accountName = $data['account_name'];
                $paystackCustomerCode = $data['customer']['customer_code'] ?? null; // Get customer code from API response

                // Store virtual account details in the database
                $account = Account::create([
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'account_number' => $accountNumber,
                    'bank_name' => $bankName,
                    'bank_number' => (string) $bankId, // Store bank ID as string
                    'paystack_customer_code' => $paystackCustomerCode, // Store Paystack customer code
                ]);

                DB::commit();
                return $account;
            } else {
                $message = $response['message'] ?? 'Failed to create virtual account with Paystack.';
                Log::error('Paystack Virtual Account Creation Failed: ' . $message, ['user_id' => $user->id, 'response' => $response]);
                DB::rollBack();
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error generating virtual account: ' . $e->getMessage(), ['user_id' => $user->id, 'exception' => $e]);
            DB::rollBack();
            return null;
        }
    }
}
