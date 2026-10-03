<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MerchantController extends Controller
{
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

    public function create(): View
    {
        return view('merchants.create');
    }
    
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

    public function show(Merchant $merchant): View
    {
        $merchant->load('plans');

        return view('merchants.show', [
            'merchant' => $merchant,
        ]);
    }
}
