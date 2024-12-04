@extends('frontend.layouts.master')
@section('content')
    <section class="wrap__section py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-body p-5">
                            <div class="text-center">
                                <img src="{{ asset('frontend/assets/images/mail-icon.png') }}" alt="Email Verification"
                                    class="img-fluid mb-4" style="max-width: 150px;">

                                <h2 class="fw-bold text-primary mb-3">{{ __('frontend.Verify Your Email Address') }}</h2>

                                @if (session('status') == 'verification-link-sent')
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        {{ __('frontend.A new verification link has been sent to your email address.') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <p class="mb-4">
                                    {{ __('frontend.Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                                </p>

                                <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ request('email') }}">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('frontend.Resend Verification Email') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
