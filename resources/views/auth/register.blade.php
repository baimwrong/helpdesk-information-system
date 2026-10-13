@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-md-5">
        <h4 class="fw-bold mb-3">Create Customer Account</h4>
        <p class="text-secondary small mb-4">Register to submit and track support tickets.</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label small fw-semibold text-secondary">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="name" id="name" class="form-control border-start-0" value="{{ old('name') }}" required autofocus placeholder="John Doe">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control border-start-0" value="{{ old('email') }}" required placeholder="john.doe@company.internal">
                </div>
            </div>

            <div class="mb-3">
                <label for="department_id" class="form-label small fw-semibold text-secondary">Your Department</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-building"></i></span>
                    <select name="department_id" id="department_id" class="form-select border-start-0">
                        <option value="">-- Select Your Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label small fw-semibold text-secondary">Phone / Extension</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-phone"></i></span>
                    <input type="text" name="phone" id="phone" class="form-control border-start-0" value="{{ old('phone') }}" placeholder="+1-555-0199 or Ext. 402">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">Password (min 8 chars)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0" required placeholder="••••••••">
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-shield-halved"></i></span>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-start-0" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-user-plus me-2"></i>Create Account
            </button>
        </form>

        <div class="text-center mt-4">
            <span class="text-secondary small">Already have an account?</span>
            <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none">Sign In</a>
        </div>
    </div>
</div>
@endsection
