    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">



        <title>Login Page</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">


    <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: #f4f4f4;
            }
            .container {
                position: relative;
                width: 500px;
                height: 550px;
                display: flex;
                margin-left: 400px;
                align-items: center;
            }
            .welcome-box {
                width: 100%;
                height: 100%;
                background: #E00024;
                border-radius: 20px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                color: white;
                text-align: center;
                padding: 20px;
            }
            .welcome-box h2 {
                margin-bottom: 10px;
                font-size: 24px;
            }
            .welcome-box p {
                font-size: 14px;
                opacity: 0.9;
                margin-bottom: 20px;
            }
            .login-box {
                position: absolute;
                background: #FFFFFF;
                width: 400px;
                height: 470px;
                padding: 35px;
                margin-left: -380px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                left: 30px;
            }
            .login-box h3 {
                text-align: center;
                margin-bottom: 50px;
                color: #E00024;
            }
            .input-group {
                display: flex;
                background: #F1F1F1;
                padding: 10px;
                border-radius: 5px;
                margin-top: 20px;
                height: 50px;
                margin-bottom: 20px;
                align-items: center;
            }
            .input-group i {
                margin-right: 10px;
                color: gray;
            }
            .input-group input {
                border: none;
                outline: none;
                background: transparent;
                flex: 1;
                font-size: 14px;
            }
            .login-btn {
                width: 50%;
                background: #E00024;
                color: white;
                border: none;
                padding: 10px;
                margin-left: 80px;
                margin-top: 20px;
                cursor: pointer;
                font-weight: bold;
                border-radius: 10px;
            }
            .login-btn:hover {
                background: #E87F2E;
            }

            button.toggle-password {
                background: transparent;
                border: none;
                padding: 0;
                cursor: pointer;
            }
            .register-btn {
                width: 50%;
                background: #0A1F7C;
                color: white;
                border: none;
                padding: 10px;
                margin-top: 20px;
                cursor: pointer;
                font-weight: bold;
                border-radius: 10px;
            }
            .register-btn:hover {
                background: #00A9FF;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="welcome-box">
                <h2>WELCOME!</h2>

            </div>
            <div class="login-box">
                <h3 class="login">Login please</h3>

                <!-- Display login errors -->
                        @if($errors->any())
                <div style="color: red; text-align: center;">
                    {{ $errors->first('login') }}
                </div>
            @endif


                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="input-group">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" placeholder="Input your Email" required value="{{ old('email') }}">
                    </div>
                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Input your password" required>
                        <button type="button" class="toggle-password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <button type="submit" class="login-btn">LOG IN</button>
                </form>
            </div>
        </div>

        <script>
            const togglePasswordBtn = document.querySelector('.toggle-password');
            const passwordInput = document.querySelector('#password');

            togglePasswordBtn.addEventListener('click', function () {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        </script>
        </body>
    </html>
