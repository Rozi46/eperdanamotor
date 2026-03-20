@extends('admin.AdminOne.cashier.layout.assets')
@section('title', 'Login Cashier')

@section('login')

        <div class="assets_page">
            <div class="container-fluid text-center">
                <div class="row content_login">
                    <div class="container">
                        <div class="row text-center">
                            <div class="col-md-12 bg_form_login">
                                <div class="col-md-4 offset-md-4 form_login">
                                    <div class="logo">
                                        <img src="{{url('/image/public/logo.png')}}"  alt="Logo"/>
                                    </div>
                                    <div class="head">Masuk Cashier</div>
                                    <div class="tag">Masuk dengan alamat email dan kata sandi Anda</div>
                                    <div class="error_alert">
                                        @if(session('error'))
                                            <div class="col-md-12 alert alert-danger text-left" role="alert">
                                                @if (session('error') == 'error_jwt')
                                                    @lang('lang.error_jwt')
                                                @elseif (session('error') == 'error_login_username')
                                                    @lang('lang.error_login_username')
                                                @elseif (session('error') == 'error_login_password')
                                                    @lang('lang.error_login_password')
                                                @else
                                                    {{ session('error') }}
                                                @endif
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        @endif
                                        @if (count($errors) > 0)
                                            @foreach ($errors->all() as $error)
                                                <div class="col-md-12 alert alert-danger text-left" role="alert">
                                                    {{ucfirst(strtolower($error))}}
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <form method="post" name="action_form" enctype="multipart/form-data" action='login'>
                                        {{csrf_field()}}
                                        <div class="input_login text-left">
                                            <div class="title">Alamat Email</div>
                                            <input type="text" name="email" placeholder="Alamat Email*" value="{{ old('email') }}" autocomplete="off" autofocus/>
                                        </div>
                                        <div class="input_login text-left">
                                            <div class="title">Kata Sandi</div>
                                            <div class="password-container">
                                                <input type="password" name="password" id="password" placeholder="Kata Sandi*" value="{{ old('password') }}"/>
                                                <i class="fa fa-eye-slash toggle-password" id="toggle-password" onclick="togglePassword()"></i>
                                            </div>
                                        </div>

                                        <div class="input_login text-center">
                                            <button type="submit" class="btn">Masuk</button>
                                        </div>
                                    </form>
                                    <div class="footer_login">
                                        tokuva © <?php 
                                        $currentYear = date('Y');
                                        echo ($currentYear == 2024) ? '2024' : '2024 - ' . $currentYear;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @section('script')
            <script type="text/javascript">
                $(document).ready(function(){
                });
                
                function togglePassword() {
                    var passwordField = document.getElementById("password");
                    var togglePasswordIcon = document.getElementById("toggle-password");
                    var passwordFieldType = passwordField.getAttribute("type");                    

                    if (passwordFieldType === "password") {
                        passwordField.setAttribute("type", "text");
                        togglePasswordIcon.classList.remove("fa-eye-slash");
                        togglePasswordIcon.classList.add("fa-eye");
                    } else {
                        passwordField.setAttribute("type", "password");
                        togglePasswordIcon.classList.remove("fa-eye");
                        togglePasswordIcon.classList.add("fa-eye-slash");
                    }
                }

            </script>
        @endsection
@endsection