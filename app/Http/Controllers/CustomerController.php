<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Merchant $merchant): View
    {
        $customers = $merchant->customers()
            ->latest()
            ->paginate(20);

        return view('customers.index', [
            'merchant' => $merchant,
            'customers' => $customers,
        ]);
    }

    public function create(Merchant $merchant): View
    {
        return view('customers.create', [
            'merchant' => $merchant,
        ]);
    }

    public function store(
        Request $request,
        Merchant $merchant
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        $merchant->customers()->create($validated);

        return redirect()
            ->route(
                'merchants.customers.index',
                $merchant
            )
            ->with(
                'success',
                'Customer created successfully.'
            );
    }
}