<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        $goals = Auth::user()->goals()->withSum('transactions', 'amount')->paginate(9);

        return view('goals.index', compact('goals'));
    }

    public function store(Request $request)

    {
        $attributes = $request->validate([
            'amount' => ['required', 'numeric'],
            'description' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if ($attributes['amount'] <= 0) {
            return redirect('/dashboard')->with('error', 'Can\'t be zero or negative');
        }

        $goal = new Goal([
            'amount' => $attributes['amount'],
            'description' => $attributes['description'],
        ]);

        $user->goals()->save($goal);

        return redirect('/dashboard');
    }

    public function invest(Request $request, Goal $goal)
    {
        $attributes = $request->validate([
            'type' => ['required', 'regex:/^1$/'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string'],
        ]);

        $response = back();

        $user = Auth::user();

        $balance = Transaction::where('user_id', $user->id)->sum('amount');

        if ($attributes['amount'] > $balance) {
            return redirect('/goals')->with('error', 'You don\'t have enough money for this transaction');
        }

        if ($attributes['amount'] > $goal->amount + $goal->transactions()->sum('amount')) {
            return redirect('/goals')->with('error', 'Your goal requires less');
        }

        if ((-$goal->transactions->sum('amount') + $attributes['amount']) >= $goal->amount) {
            $response->with([
                'success' => 'You achieved your ' . $goal->description . ' goal.'
            ]);
        }

        $transaction = new Transaction([
            'amount' => -$attributes['amount'],
            'description' => $attributes['description'],
        ]);

        $transaction->user()->associate($user);

        $goal->transactions()->save($transaction);

        return $response;
    }

    public function show(Goal $goal)
    {
        $goal = Auth::user()->goals()->where('id', $goal->id)->withSum('transactions', 'amount')->firstOrFail();

        return view('goals.show', compact('goal'));
    }

    public function edit(Goal $goal)
    {
        $amount = request()->input('amount');

        $goal->update(['amount' => $amount]);

        return redirect('/goals');
    }

    public function destroy(Goal $goal)
    {
        $goal->transactions()->delete();

        $goal->delete();

        return redirect('/goals')->with('success', 'Goal ' . $goal->description . '  successfully deleted.');
    }
}
