<!DOCTYPE html>

<html lang="en">

<head>
    <title>Gadget Sign Up Form</title>
    <!-- Meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Gadget Sign Up Form" />
    <script>
        addEventListener("load", function () { setTimeout(hideURLbar, 0); }, false); function hideURLbar() { window.scrollTo(0, 1); }
    </script>
    <!-- Meta tags -->
    <!-- font-awesome icons -->
    <link href="/css/font-awesome.min.css" rel="stylesheet">
    <!-- //font-awesome icons -->
    <!--stylesheets-->
    <link href="/css/style.css" rel='stylesheet' type='text/css' media="all">
    <!--//style sheet end here-->
    <link href="/css/fonts-googleapis-300-400-500-600.css" rel="stylesheet">
    <link href="/css/fonts-googleapis-400-600-700.css" rel="stylesheet">
</head>
<body>
    <h1 class="error">Gadget Sign Up Form</h1>
	<!---728x90--->
    <div class="w3layouts-two-grids">
	<!---728x90--->
        <div class="mid-class">
            <div class="img-right-side">
                <h3>Manage Your Gadgets Account</h3>
                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget Lorem ipsum dolor sit
                    amet, consectetuer adipiscing elit. Aenean commodo ligula ege</p>
                <img src="/images/b11.png" class="img-fluid" alt="">
            </div>
            <div class="txt-left-side">
                <h2> Sign Up Here </h2>
                
                <form action="{{ url('/admin/login') }}" method="post">
                    @csrf
                    <!-- <div class="form-left-to-w3l">
                        <span class="fa fa-user-o" aria-hidden="true"></span>
                        <input type="text" name="Name" placeholder=" Name" required="">

                        <div class="clear"></div>
                    </div> -->
                    <!-- <div class="form-left-to-w3l">
                        <span class="fa fa-phone" aria-hidden="true"></span>
                        <input type="text" name="Phone" placeholder="Phone" required="">

                        <div class="clear"></div>
                    </div> -->
                    <div class="form-left-to-w3l">
                        <span class="fa fa-envelope-o" aria-hidden="true"></span>
                        <input type="email" name="email" placeholder="账号" required="" autocomplete="off">
                        <div class="clear"></div>
                    </div>
                    @error('Email')
                        <div class="invalid-feedback" style="color: red;margin: 5px 70px;text-align: left;">
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="form-left-to-w3l ">
                        <span class="fa fa-lock" aria-hidden="true"></span>
                        <input type="password" name="password" placeholder="密码" required="" autocomplete="off">
                        <div class="clear"></div>
                    </div>
                    <div class="form-left-to-w3l ">
                        <span class="fa fa-google" aria-hidden="true"></span>
                        <input type="text" name="google2fa_code" placeholder="Google验证码" autocomplete="off">
                        <div class="clear"></div>
                    </div>
                    @error('Google2fa')
                        <div class="invalid-feedback" style="color: red;margin: 5px 70px;text-align: left;">
                            {{ $message }}
                        </div>
                    @enderror
                    <!-- <div class="main-two-w3ls">
                        <div class="left-side-forget">
                            <input type="checkbox" class="checked">
                            <span class="remenber-me">记住账号</span>
                        </div>
                        <div class="right-side-forget">
                            <a href="#" class="for">忘记密码?</a>
                        </div>
                    </div> -->
                    <div class="btnn">
                        <button type="submit">登 录</button>
                    </div>
                </form>
                <!-- <div class="w3layouts_more-buttn">
                    <h3>Don't Have an account..?
                        <a href="#">Login Here
                        </a>
                    </h3>
                </div> -->
                <div class="clear"></div>
            </div>
        </div>
    </div>
	<!---728x90--->
    <footer class="copyrigh-wthree">
        <p>
            © 2025 Gadget Sign Up Form. All Rights Reserved
        </p>
    </footer>
</body>

</html>