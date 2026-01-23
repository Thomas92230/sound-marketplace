<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Payout;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtistDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $tracks = Track::where('user_id', $user->id)
            ->latest()
            ->get();

        $completedSales = Purchase::query()
            ->where('status', 'completed')
            ->whereHas('track', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['track', 'user'])
            ->latest()
            ->paginate(20, ['*'], 'sales_page');

        $salesCount = Purchase::query()
            ->where('status', 'completed')
            ->whereHas('track', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        $grossRevenueCents = Purchase::query()
            ->where('status', 'completed')
            ->whereHas('track', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->sum('amount_cents');

        $payouts = Payout::where('user_id', $user->id)
            ->with(['purchase.track'])
            ->latest()
            ->paginate(20, ['*'], 'payouts_page');

        $pendingPayoutsCents = Payout::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount_cents');

        $paidPayoutsCents = Payout::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount_cents');

        return view('artist.dashboard', [
            'tracks' => $tracks,
            'completedSales' => $completedSales,
            'salesCount' => $salesCount,
            'grossRevenueCents' => (int) $grossRevenueCents,
            'payouts' => $payouts,
            'pendingPayoutsCents' => (int) $pendingPayoutsCents,
            'paidPayoutsCents' => (int) $paidPayoutsCents,
        ]);
    }
}

