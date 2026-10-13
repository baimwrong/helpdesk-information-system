@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-md-5">
        <h4 class="fw-bold mb-3">Sign In to Your Account</h4>
        <p class="text-secondary small mb-4">Enter your credentials to access tickets and services.</p>

        @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" id="loginForm">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control border-start-0" value="{{ old('email') }}" required autofocus placeholder="name@company.internal">
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0" required placeholder="••••••••">
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label small text-secondary" for="remember">Remember this device</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <p class="small text-secondary mb-2 fw-semibold">Demo Quick Login Credentials:</p>
            <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-xs btn-outline-danger small py-1 px-2" style="font-size: 0.75rem;" onclick="fillLogin('admin@helpdesk.local', 'password123')">
                    <i class="fa-solid fa-shield-halved me-1"></i>Admin
                </button>
                <button type="button" class="btn btn-xs btn-outline-success small py-1 px-2" style="font-size: 0.75rem;" onclick="fillLogin('agent.john@helpdesk.local', 'password123')">
                    <i class="fa-solid fa-headset me-1"></i>Agent (IT)
                </button>
                <button type="button" class="btn btn-xs btn-outline-primary small py-1 px-2" style="font-size: 0.75rem;" onclick="fillLogin('user.alice@example.com', 'password123')">
                    <i class="fa-solid fa-user me-1"></i>Customer
                </button>
            </div>
        </div>

        <div class="text-center mt-4">
            <span class="text-secondary small">Need an account?</span>
            <a href="{{ route('register') }}" class="small fw-semibold text-decoration-none">Register as Customer</a>
        </div>
    </div>
</div>

<script>
    function fillLogin(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endsection
