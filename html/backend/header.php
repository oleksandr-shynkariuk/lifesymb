<?php include_once('main.php'); ?>

<div id="header_inner_div"><div id="header_inner_left_div">

<a href="#about">About</a>

<?php

if(isset($_SESSION['logged_in']))
{
	echo ' | <a href="#help">Help</a>';
}

?>

</div><div id="header_inner_center_div">

<?php

if(isset($_SESSION['logged_in']))
{
	echo '<b>Week ' . global_week_number . ' - ' . global_day_name . ' ' . date('jS F Y') . '</b>';
}

?>

</div><div id="header_inner_right_div">

<?php

if(isset($_SESSION['logged_in']))
{
    /*enable facebook logout if logged in*/
    ?>
    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function() {
            // init the FB JS SDK
            FB.init({
                appId      : '1420863004804132',                        // App ID from the app dashboard
                channelUrl : '//www.lifesymb.com/backend/channel.html', // Channel file for x-domain comms
                status     : true,                                 // Check Facebook Login status
                xfbml      : true                                  // Look for social plugins on the page
            });

            // Additional initialization code such as adding Event Listeners goes here
        };

        // Load the SDK asynchronously
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>


    <?php
    if(isset($_SESSION['user_name'])){
        $username = $_SESSION['user_name'];
        echo '<a href="#cp">' . $username . '</a> | <a href="#logout">Log out</a>';
    }
    else
	    echo '<a href="#cp">Control panel</a> | <a href="#logout">Log out</a>';
}
else
{
	echo 'Not logged in';
}

?>

</div></div>
