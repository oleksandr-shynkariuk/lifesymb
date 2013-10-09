<?php

include_once('main.php');

if(isset($_GET['login']))
{
    $user_email = mysql_real_escape_string($_POST['user_email']);
	$user_password = mysql_real_escape_string($_POST['user_password']);
	$user_remember = $_POST['user_remember'];
	echo login($user_email, $user_password, $user_remember);
}
elseif(isset($_GET['fb_login']))
{
    $fb_id = mysql_real_escape_string($_POST['fb_id']);
    $fb_name = mysql_real_escape_string($_POST['fb_name']);
    $fb_username = mysql_real_escape_string($_POST['fb_username']);
    echo fb_login($fb_id, $fb_name, $fb_username);
}
elseif(isset($_GET['logout']))
{
	logout();
}
elseif(isset($_GET['create_user']))
{
	$user_name = mysql_real_escape_string(trim($_POST['user_name']));
	$user_email = mysql_real_escape_string($_POST['user_email']);
	$user_password = mysql_real_escape_string($_POST['user_password']);
	$user_secret_code = $_POST['user_secret_code'];
	echo create_user($user_name, $user_email, $user_password, $user_secret_code);
}
elseif(isset($_GET['new_user']))
{

?>

	<div class="box_div" id="login_div"><div class="box_top_div"><a href="#">Start</a> &gt; New user</div><div class="box_body_div">
	<div id="new_user_div"><div>

	<form action="." id="new_user_form"><p>

	<label for="user_name_input">Name:</label><br>
	<input type="text" id="user_name_input"><br><br>
	<label for="user_email_input">Email:</label><br>
	<input type="text" id="user_email_input" autocapitalize="off"><br><br>
	<label for="user_password_input">Password:</label><br>
	<input type="password" id="user_password_input"><br><br>
	<label for="user_password_confirm_input">Confirm password:</label><br>
	<input type="password" id="user_password_confirm_input"><br><br>

<?php

	if(global_secret_code != '0')
	{
		echo '<label for="user_secret_code_input">Secret code: <sup><a href="." id="user_secret_code_a" tabindex="-1">What\'s this?</a></sup></label><br><input type="password" id="user_secret_code_input"><br><br>';
	}

?>

	<input type="submit" value="Create user">

	</p></form>

	</div><div>

	<p class="blue_p bold_p">Information:</p>
	<ul>
	<li>With just a click you can make your reservation</li>
	<li>Your usage is stored automatically</li>
	<li>Your password is encrypted and can't be read</li>
	</ul>

	<div id="user_secret_code_div">Secret code is used to only allow certain people to create a new user. Contact the webmaster by email at <span id="email_span"></span> to get the secret code.</div>

	<script type="text/javascript">$('#email_span').html('<a href="mailto:'+$.base64.decode('<?php echo base64_encode(global_webmaster_email); ?>')+'">'+$.base64.decode('<?php echo base64_encode(global_webmaster_email); ?>')+'</a>');</script>

	</div></div>

	<p id="new_user_message_p"></p>

	</div></div>

<?php

}
elseif(isset($_GET['forgot_password']))
{

?>

	<div class="box_div" id="login_div"><div class="box_top_div"><a href="#">Start</a> &gt; Forgot password</div><div class="box_body_div">

	<p>Contact one of the admins below by email and write that you've forgotten your password, and you will get a new one. The password can be changed after logging in.</p>

	<?php echo list_admin_users(); ?>

	</div></div>

<?php

}
else
{

?>

	<div class="box_div" id="login_div"><div class="box_top_div">Log in</div><div class="box_body_div">
    <!--Social login-->
    <div class="box_body_div_right">
        <div id="fb-root"></div>
        <script>
            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '1420863004804132', // App ID
                    channelUrl : '//www.lifesymb.com/backend/channel.html', // Channel File
                    status     : true, // check login status
                    cookie     : true, // enable cookies to allow the server to access the session
                    xfbml      : true,  // parse XFBML
                    oath       : true
                });

                // Here we subscribe to the auth.authResponseChange JavaScript event. This event is fired
                // for any authentication related change, such as login, logout or session refresh. This means that
                // whenever someone who was previously logged out tries to log in again, the correct case below
                // will be handled.
                FB.Event.subscribe('auth.authResponseChange', function(response) {
                    // Here we specify what we do with the response anytime this event occurs.
                    if (response.status === 'connected') {
                        // The response object is returned with a status field that lets the app know the current
                        // login status of the person. In this case, we're handling the situation where they
                        // have logged in to the app.
                        testAPI();
                    } else if (response.status === 'not_authorized') {
                        // In this case, the person is logged into Facebook, but not into the app, so we call
                        // FB.login() to prompt them to do so.
                        // In real-life usage, you wouldn't want to immediately prompt someone to login
                        // like this, for two reasons:
                        // (1) JavaScript created popup windows are blocked by most browsers unless they
                        // result from direct interaction from people using the app (such as a mouse click)
                        // (2) it is a bad experience to be continually prompted to login upon page load.
                        FB.login();
                    } else {
                        // In this case, the person is not logged into Facebook, so we call the login()
                        // function to prompt them to do so. Note that at this stage there is no indication
                        // of whether they are logged into the app. If they aren't then they'll see the Login
                        // dialog right after they log in to Facebook.
                        // The same caveats as above apply to the FB.login() call here.
                        FB.login();
                    }
                });
            };

            // Load the SDK asynchronously
            (function(d){
                var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement('script'); js.id = id; js.async = true;
                js.src = "//connect.facebook.net/en_US/all.js";
                ref.parentNode.insertBefore(js, ref);
            }(document));

            // Here we run a very simple test of the Graph API after login is successful.
            // This testAPI() function is only called in those cases.
            function testAPI() {
                console.log('Welcome!  Fetching your information.... ');
                FB.api('/me', function(response) {
                    //console.log('Good to see you, ' + response.name + '.');
                    var id = response.id;
                    var name = response.name;
                    var username = response.username;

                    console.log('Good to see you, ' + response.name + ' with id=' + id + ' and username=' + username);
                    fb_login(id, name, username);
                });
            }
        </script>

        <!--
          Below we include the Login Button social plugin. This button uses the JavaScript SDK to
          present a graphical Login button that triggers the FB.login() function when clicked.

          Learn more about options for the login button plugin:
          /docs/reference/plugins/login/ -->

        <fb:login-button show-faces="true" width="200" max-rows="1"></fb:login-button>
    </div>

    <!--LifeSymb native login-->
    <div class="box_body_div_left">
	<form action="." id="login_form" autocomplete="off"><p>

	<label for="user_email_input">Email:</label><br><input type="text" id="user_email_input" value="<?php /*echo get_login_data('user_email'); */?>" autocapitalize="off"><br><br>
	<label for="user_password_input">Password:</label><br><input type="password" id="user_password_input" value="<?php /*echo get_login_data('user_password'); */?>"><br><br>
	<input type="checkbox" id="remember_me_checkbox" checked="checked"> <label for="remember_me_checkbox">Remember me</label><br><br>
	<input type="submit" value="Log in">

	</p></form>
	<p id="login_message_p"></p>
	<p><a href="#new_user">New user</a> | <a href="#forgot_password">Forgot password</a></p>
    </div>
	</div>
    </div>
<?php

}

?>
