<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaPlanController extends Controller
{
    public function index(): View
    {
        $plans = SlaPlan::withCount('tickets')
            ->orderBy('priority')
            ->paginate(15);

        return view('admin.sla-plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.sla-plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'response_time_hours' => ['required', 'integer', 'min:1'],
            'resolution_time_hours' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        SlaPlan::create([
            'name' => $validated['name'],
            'priority' => $validated['priority'],
            'response_time_hours' => $validated['response_time_hours'],
            'resolution_time_hours' => $validated['resolution_time_hours'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.sla.index')->with('success', 'SLA Policy created successfully.');
    }

    public function edit(SlaPlan $sla): View
    {
        return view('admin.sla-plans.edit', compact('sla'));
    }

    public function update(Request $request, SlaPlan $sla): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'response_time_hours' => ['required', 'integer', 'min:1'],
            'resolution_time_hours' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $sla->update([
            'name' => $validated['name'],
            'priority' => $validated['priority'],
            'response_time_hours' => $validated['response_time_hours'],
            'resolution_time_hours' => $validated['resolution_time_hours'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.sla.index')->with('success', 'SLA Policy updated successfully.');
    }

    public function destroy(SlaPlan $sla): RedirectResponse
    {
        if ($sla->tickets()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete SLA policy assigned to existing tickets.']);
        }

        $sla->delete();

        return redirect()->route('admin.sla.index')->with('success', 'SLA Policy deleted.');
    }
}
