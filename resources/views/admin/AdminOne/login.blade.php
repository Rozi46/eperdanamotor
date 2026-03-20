@extends('admin.AdminOne.layout.assets')
@section('title', 'Login Administration')

@section('login')

    <div class="login-container form_login">    
        <div class="logo">
            <img src="{{url('/themes/admin/AdminOne/image/public/logo.png')}}"  alt="Logo"/>
        </div>

        <h2>Login</h2>  

        <div class="error_alert">
            @if(session('error'))
                <div class="alert">
                    @if (session('error') == 'error_jwt')
                        @lang('lang.error_jwt')
                    @elseif (session('error') == 'error_login_username')
                        @lang('lang.error_login_username')
                    @elseif (session('error') == 'error_login_password')
                        @lang('lang.error_login_password')
                    @else
                        {{ session('error') }}
                    @endif
                    <span class="close">&times;</span>
                </div>
            @endif
            @if (count($errors) > 0)
                @foreach ($errors->all() as $error)
                    <div class="alert">
                        {{ucfirst(strtolower($error))}}
                        <span class="close">&times;</span>
                    </div>
                @endforeach
            @endif
        </div>

        <form method="post" name="action_form" enctype="multipart/form-data" action='/admin/login'>
            {{csrf_field()}}

            <div class="floating-label">
                <input type="text" name="email" placeholder=" " required>
                <label for="email">Email </label>
            </div>
            <div class="floating-label">
                <input type="password" name="password" id="password" placeholder=" " required>
                <label for="password">Password </label>
            </div>
            <div class="show-password">
                <input type="checkbox" class="form-check-input" id="showPassword" onclick="togglePassword()">
                <label class="form-check-label" for="showPassword">Tampilkan Kata Sandi</label>
            </div>

            <button type="submit">Masuk</button>
        </form>

        <div class="login-footer">
        <!-- <p>Belum punya akun? <a href="#">Daftar</a></p> -->
        <!-- <p><a href="#">Lupa Password?</a></p> -->
        <p>© {{ now()->year == 2021 ? '2021' : '2021 - ' . now()->year }}</p>
        </div>
    </div>

    @section('script')
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                const closeButtons = document.querySelectorAll(".error_alert .close");

                closeButtons.forEach(function(btn) {
                    btn.addEventListener("click", function() {
                        const alertBox = this.parentElement;
                        alertBox.style.opacity = "0";   // animasi fade out
                        setTimeout(() => alertBox.style.display = "none", 100); // hilang setelah 300ms
                    });
                });
            });

            function togglePassword() {
                var passwordField = document.getElementById("password");
                var showPasswordCheckbox = document.getElementById("showPassword");

                if (showPasswordCheckbox.checked) {
                    passwordField.setAttribute("type", "text");
                    // passwordField.style.backgroundColor = "#f9f9f9"; // sedikit perubahan warna saat show
                } else {
                    passwordField.setAttribute("type", "password");
                    // passwordField.style.backgroundColor = "#fff"; // kembali normal
                }
            }
        </script>
    @endsection
        
@endsection