<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CannedResponseController extends Controller
{
    public function index(): View
    {
        $responses = CannedResponse::with(['user', 'department'])
            ->latest()
            ->paginate(15);

        return view('admin.canned-responses.index', compact('responses'));
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.canned-responses.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'shortcut' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'content' => ['required', 'string'],
            'is_shared' => ['boolean'],
        ]);

        CannedResponse::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'shortcut' => $validated['shortcut'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'content' => $validated['content'],
            'is_shared' => $request->boolean('is_shared', true),
        ]);

        return redirect()->route('admin.canned.index')->with('success', 'Canned response template created.');
    }

    public function edit(CannedResponse $canned): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.canned-responses.edit', compact('canned', 'departments'));
    }

    public function update(Request $request, CannedResponse $canned): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'shortcut' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'content' => ['required', 'string'],
            'is_shared' => ['boolean'],
        ]);

        $canned->update([
            'title' => $validated['title'],
            'shortcut' => $validated['shortcut'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'content' => $validated['content'],
            'is_shared' => $request->boolean('is_shared'),
        ]);

        return redirect()->route('admin.canned.index')->with('success', 'Canned response template updated.');
    }

    public function destroy(CannedResponse $canned): RedirectResponse
    {
        $canned->delete();

        return redirect()->route('admin.canned.index')->with('success', 'Canned response template deleted.');
    }
}
