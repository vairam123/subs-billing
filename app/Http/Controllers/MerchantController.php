<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MerchantController extends Controller
{
    /**
    * List merchants with their plan counts.
    */
    public function index(): View
    {
        $merchants = Merchant::query()
            ->withCount(['plans'])
            ->latest()
            ->get();

        return view('merchants.index', [
            'merchants' => $merchants,
        ]);
    }

    /**
    * Display the merchant creation form.
    */
    public function create(): View
    {
        return view('merchants.create');
    }
    
    /**
    * Validate and create a merchant.
    */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $merchant = Merchant::create($validated);

        return redirect()
            ->route('merchants.show', $merchant)
            ->with('success', 'Merchant created successfully.');
    }

    /**
    * Display the merchant and its configured plans.
    */
    public function show(Merchant $merchant): View
    {
        $merchant->load('plans');

        return view('merchants.show', [
            'merchant' => $merchant,
        ]);
    }
}
