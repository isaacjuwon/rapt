<?php

namespace App\Livewire\Admin\Account;

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Actions\Account\GenerateVirtualAccount;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function generateAccount(User $user, GenerateVirtualAccount $generateVirtualAccount)
    {
        if ($user->accounts()->exists()) {
            Session::flash('error', 'User already has a virtual account.');
            return;
        }

        $account = $generateVirtualAccount->execute($user);

        if ($account) {
            Session::flash('success', 'Virtual account generated successfully for ' . $user->name . '.');
        } else {
            Session::flash('error', 'Failed to generate virtual account for ' . $user->name . '.');
        }
    }

    public function with(): array
    {
        $users = User::with('accounts')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return [
            'users' => $users,
        ];
    }
}
