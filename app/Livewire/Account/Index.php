<?php

namespace App\Livewire\Account;

use Livewire\Volt\Component;
use App\Actions\Account\GenerateVirtualAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $virtualAccount = null;

    public function mount()
    {
        $this->virtualAccount = Auth::user()->accounts()->first();
    }

    public function generateAccount(GenerateVirtualAccount $generateVirtualAccount)
    {
        $user = Auth::user();

        if ($user->accounts()->exists()) {
            Session::flash('error', 'You already have a virtual account.');
            return;
        }

        $account = $generateVirtualAccount->execute($user);

        if ($account) {
            $this->virtualAccount = $account;
            Session::flash('success', 'Virtual account generated successfully!');
        } else {
            Session::flash('error', 'Failed to generate virtual account. Please try again.');
        }
    }

    public function with(): array
    {
        return [
            'user' => Auth::user(),
        ];
    }
}
