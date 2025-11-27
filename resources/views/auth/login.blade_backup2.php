<style>
    .container-fluid {
        background: linear-gradient(90deg, hsla(185, 64%, 51%, 1) 0%, hsla(277, 74%, 24%, 1) 100%);
        height: 100%;
        width: 100%;
        padding: 30px;
    }

    .card2 {
        width: 1280px;
        margin: auto;
        -webkit-box-shadow: 0px 0px 0px 8px #000000, 0px 0px 0px 16px #4B4C4B, 0px 0px 0px 24px #828482, 0px 0px 0px 31px #B2B5B2, 0px 0px 0px 39px #DADDDA, 5px 5px 15px 5px rgba(0, 0, 0, 0);
        box-shadow: 0px 0px 0px 8px #000000, 0px 0px 0px 16px #4B4C4B, 0px 0px 0px 24px #828482, 0px 0px 0px 31px #B2B5B2, 0px 0px 0px 39px #DADDDA, 5px 5px 15px 5px rgba(0, 0, 0, 0);
    }

    /* .facebook {
        background-color: #3b5998;
        color: #fff;
        font-size: 18px;
        padding-top: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        cursor: pointer;
    }

    .twitter {
        background-color: #1DA1F2;
        color: #fff;
        font-size: 18px;
        padding-top: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        cursor: pointer;
    }

    .linkedin {
        background-color: #2867B2;
        color: #fff;
        font-size: 18px;
        padding-top: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        cursor: pointer;
    } */

    .wrapper {
        max-width: 450px;
        margin: 0 auto;
        padding: 20px 15px;
        border-radius: 20px;
        background-color: #03A9F4;
    }

    .logo2 {
        width: 80px;
        margin: auto;
    }

    .logo2 img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 50%;
        box-shadow: 0px 0px 3px #5f5f5f,
            0px 0px 0px 5px #ecf0f3,
            8px 8px 15px #a7aaa7,
            -8px -8px 15px #fff;
    }

    .wrapper .name {
        font-weight: 900;
        font-size: 1.4rem;
        letter-spacing: 1.3px;
        padding-left: 10px;
        color: #000;
    }

    .wrapper .form-field .fas {
        color: #000;
    }

    .wrapper .btn {
        box-shadow: none;
        width: 100%;
        height: 40px;
        background: linear-gradient(90deg, hsla(185, 64%, 51%, 1) 0%, hsla(277, 74%, 24%, 1) 100%);
        color: #fff;
        border-radius: 20px 0 20px 0;
        box-shadow: 3px 3px 3px #b1b1b1,
            -3px -3px 3px #fff;
        letter-spacing: 1.3px;
    }

    .wrapper .btn:hover {
        background: linear-gradient(90deg, hsla(277, 74%, 24%, 1) 0%, hsla(185, 64%, 51%, 1) 100%);
    }

    .wrapper a {
        text-decoration: none;
        font-size: 0.8rem;
        color: #fff;
        cursor: pointer;
    }

    .wrapper a:hover {
        color: #30343F;
    }


    .pad.s {
        box-shadow: 0px 1px 2px #eee, 0px 2px 2px #e9e9e9, 0px 3px 2px #ccc, 0px 4px 2px #c9c9c9, 0px 5px 2px #bbb, 0px 6px 2px #b9b9b9, 0px 7px 2px #999, 0px 7px 2px rgba(0, 0, 0, .5), 0px 7px 2px rgba(0, 0, 0, 0.1), 0px 7px 2px rgba(0, 0, 0, .73), 0px 3px 5px rgba(0, 0, 0, .3), 0px 5px 10px rgba(0, 0, 0, .37), 0px 10px 10px rgba(0, 0, 0, .1), 0px 20px 20px rgba(0, 0, 0, 0.1);
    }

    @media screen and (max-width: 991px) {
        .card2 {
            border-top: 1px solid #EEEEEE !important;
            margin: 0px 15px;
        }
    }
</style>

<x-guest-layout>
    <div class="container-fluid">
        <div class="card card2">
            <div class="row d-flex">
                <div class="col-lg-6">
                    <div class="row p-5 justify-content-center border-line">
                        <img src="https://i.imgur.com/uNGdWHi.png" class="image">
                    </div>
                </div>
                <div class="col-lg-6 py-4">
                    <div class="wrapper pad s">
                        <div class="logo2">
                            <img src="https://logodownload.org/wp-content/uploads/2019/10/adobe-photoshop-logo-0.png">
                        </div>
                        <div class="text-center mt-4 mb-4 name">
                            PT. PALING SEHAT
                        </div>
                        <x-auth-session-status class="mb-3" :status="session('status')" />

                        <!-- Validation Errors -->
                        <x-auth-validation-errors class="mb-3" :errors="$errors" />
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <x-label for="username" :value="__('Username')" />

                                <x-input id="username" type="text" name="username" :value="old('username')" required
                                    autofocus />
                            </div>
                            <div class="form-group">
                                <x-label for="password" :value="__('Password')" />

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
                            <div class="mb-0">
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
                </div>
            </div>
            <div class="bg-blue py-4">
                <div class="row px-3">
                    <small class="ml-4 ml-sm-5 mb-2">Copyright &copy; 2022. All rights reserved.</small>
                    <div class="social-contact ml-4 ml-sm-auto">
                        <i class="bi bi-facebook"></i>
                        <span class="fa fa-google-plus mr-4 text-sm"></span>
                        <span class="fa fa-linkedin mr-4 text-sm"></span>
                        <span class="fa fa-twitter mr-4 mr-sm-5 text-sm"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
