<style>
    body,
    html {
        margin: 0;
        padding: 0;
        background-image: linear-gradient(360deg, hsla(16, 100%, 76%, 0.2) 0%, hsla(49, 100%, 81%, 0.2) 100%)
        , url("img/bg_6.jpg");
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }

    .bg2 {
        width: 600px;
        height: 610px;
        margin-left: -40px;
        margin-bottom: -50px;
    }

    .container {
        position: relative;
        text-align: center;
        color: white;
        padding-top: 20px;
    }

    /* Centered text */
    .centered {
        width: 30%;
        margin-left: -25px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .logo2 {
        width: 100px;
        height: 100px;
        margin: auto;
        margin-top: -120px;
        margin-bottom: 20px;
    }

    .logo2 img {
        width: 100%;
        height: 100%;
        background-color: #0061ff;
        border-radius: 50%;
        box-shadow: 0px 0px 3px #5f5f5f,
            0px 0px 0px 5px #ecf0f3,
            8px 8px 15px #a7aaa7,
            -8px -8px 15px #0061ff;
    }

    .name {
        font-size: 25pt;
        font-weight: bolder !important;
        color: #FFFFFF;
        text-shadow: 0 -1px 4px #FFF, 0 -2px 10px #ff0, 0 -10px 20px #ff8000, 0 -18px 40px #F00;
    }

    .form-group {
        margin: 0 !important;
    }

    .alert-login {
        margin-top: -70px;
    }

    @media screen and (max-width: 991px) {
        .card2 {
            border-top: 1px solid #EEEEEE !important;
            margin: 0px 15px;
        }
    }
</style>

<x-guest-layout>
    <div class="container">
        <div class="row pt-5">
            <div class="col-lg-6">
                <img src=img/bg_bulat2.png class="bg2">
                <div class="centered">

                    <div class="text-center name">
                        TIARA DEWATA PENJUALAN
                    </div>
                    <form method="POST" action="{{ route('login') }}" class="m-0">
                        @csrf
                        <div class="form-group">
                            <x-label for="username" :value="__('USERNAME')" />

                            <x-input id="username" type="text" name="username" :value="old('username')" required
                                autofocus />
                        </div>
                        <div class="form-group">
                            <x-label for="password" :value="__('PASSWORD')" />

                            <x-input id="password" type="password" name="password" required
                                autocomplete="current-password" />
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <x-checkbox id="remember_me" name="remember" />

                                <label class="form-check-label" for="remember_me">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <div class="text-center">
                                @if (Route::has('password.request'))
                                    <a class="text-white" href="{{ route('password.request') }}">
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif
                            </div>
                            <x-button>
                                {{ __('Log in') }}
                            </x-button>
                        </div>
                    </form>
                    <x-auth-session-status :status="session('status')" />
                </div>
                <x-auth-validation-errors class="alert-login" :errors="$errors" />
            </div>
            <div class="col-lg-6">
            </div>
        </div>
        <!-- Validation Errors -->
    </div>
    {{-- <div class="container-fluid">
        <div class="wrapper">
            <div class="logo2">
                <img src="https://logodownload.org/wp-content/uploads/2019/10/adobe-photoshop-logo-0.png">
            </div>
            <div class="text-center mt-4 mb-4 name">
                TIARA DEWATA
            </div>
            <x-auth-session-status class="mb-3" :status="session('status')" />

            <!-- Validation Errors -->
            <x-auth-validation-errors class="mb-3" :errors="$errors" />
            <form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
        <x-label for="username" :value="__('Username')" />

        <x-input id="username" type="text" name="username" :value="old('username')" required autofocus />
    </div>
    <div class="form-group">
        <x-label for="password" :value="__('Password')" />

        <x-input id="password" type="password" name="password" required autocomplete="current-password" />
    </div>
    <div class="form-group">
        <div class="form-check">
            <x-checkbox id="remember_me" name="remember" />

            <label class="form-check-label" for="remember_me">
                {{ __('Remember Me') }}
            </label>
        </div>
    </div>
    <div class="px-5">
        <div class="d-flex justify-content-end align-items-baseline">
            <x-button>
                {{ __('Log in') }}
            </x-button>
        </div>
    </div>
    </form>
    <div class="text-center fs-6">
        @if (Route::has('password.request'))
        <a class="text-muted mr-3" href="{{ route('password.request') }}">
            {{ __('Forgot your password?') }}
        </a>
        @endif
    </div>
    </div>
    </div> --}}
</x-guest-layout>
