@extends('layouts.app')

@section('title', 'Login')

@section('content')
<style>
    .login-container {
        min-height: 100vh;
        background: linear-gradient(135deg, rgba(255, 153, 0, 0.9), rgba(240, 135, 0, 0.9)), url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1600') center/cover no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .login-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        max-width: 450px;
        width: 100%;
    }
    
    .login-card-header {
        background: linear-gradient(135deg, #FF9900, #F08700);
        color: white;
        padding: 30px;
        text-align: center;
    }
    
    .login-card-header h3 {
        margin: 0;
        font-weight: 600;
    }
    
    .login-card-body {
        padding: 40px;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
    }
    
    .form-control {
        border-left: none;
        padding-left: 0;
    }
    
    .form-control:focus {
        border-left: none;
        box-shadow: 0 0 0 0.2rem rgba(255, 153, 0, 0.25);
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #FF9900;
        background-color: #fff;
    }
    
    .input-group:focus-within .form-control {
        border-color: #FF9900;
    }
    
    .password-toggle {
        cursor: pointer;
        background-color: #f8f9fa;
        border-left: none;
        color: #6c757d;
    }
    
    .password-toggle:hover {
        background-color: #e9ecef;
        color: #FF9900;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #FF9900, #F08700);
        border: none;
        padding: 12px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 153, 0, 0.4);
        background: linear-gradient(135deg, #F08700, #FF9900);
    }
</style>

<div class="login-container">
    <div class="login-card">
        <div class="login-card-header">
            <h3><i class="bi bi-building me-2"></i>Warehouse Management System</h3>
        </div>
        <div class="login-card-body">
            <form id="loginForm">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter your password" required>
                        <span class="input-group-text password-toggle" id="togglePassword">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn btn-login text-white w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Password Toggle
    $('#togglePassword').on('click', function() {
        const passwordInput = $('#passwordInput');
        const eyeIcon = $('#eyeIcon');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });
    
    // Login Form Submit
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Logging in...');
        
        $.ajax({
            url: '/login',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect || '/dashboard';
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: xhr.responseJSON?.message || 'Invalid email or password',
                    confirmButtonColor: '#FF9900'
                });
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush
