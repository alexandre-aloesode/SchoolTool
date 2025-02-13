<html>
    <head>
        <meta name="google-signin-client_id" content="776344449452-o05h7394ddmtomd0ga92v4p04va4bmb5.apps.googleusercontent.com">
        <!-- <meta name="google-signin-client_id" content="604347883543-cu73up3fqo5r9gn18tqpkf3tu9ud41s4.apps.googleusercontent.com"> -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
        <!-- <script src="https://apis.google.com/js/platform.js" async defer></script> -->
        <script>
            function onSignIn(googleUser) {
                var id_token = googleUser.getAuthResponse().id_token;
                console.log("id_token: "+id_token);

                $.ajax({
                    url : 'https://test-auth.laplateforme.io/oauth',
                    type : 'POST',
                    data : 'token_id=' + id_token,
                    success : function(res, statut) {
                        //req contient le token API
                        console.log(res);
                    },
                    error : function(res, statut, erreur) {
                        console.log(statut+" "+res);
                    }
                });
 
                var profile = googleUser.getBasicProfile();
                console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
                console.log('Name: ' + profile.getName());
                console.log('Image URL: ' + profile.getImageUrl());
                console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.

                $(".g-signin2").hide();
                $("#signin-message").text("Bonjour "+profile.getName());
                $("#signout").show();
            }
 
            function signOut() {
                var auth2 = gapi.auth2.getAuthInstance();
                auth2.signOut().then(function () {
                    console.log('User signed out.');
                    $("#signout").hide();
                    $("#signin-message").text("");
                    $(".g-signin2").show();
                });
            }

            $(document).ready(function() {
                $("#signout").click(function() {
                    signOut();
                });
            });
        </script>
        <style>
            html {
                height: 100%;
            }
            body{
                background-color:#88c1d0;
                font-size:14px;
                color:#fff;
                text-align:center;
            }
            .grid {
                flex-direction:column;
                align-items: center;
                display: flex;
                max-width: 1260px;
                width: 100%;
                margin: 0 auto;
                padding: 20px;
            }
            .g-signin2{
                width: 100%;
            }

            .g-signin2 > div{
                margin: 0 auto;
            }
            #title {
                margin-bottom:30px;
            }
            #signout {
                display: none;
            }
        </style>
    </head>
    <body>
        <div class="grid">
	        <section id="title" class="col-12 col-sm-12 col-md-8">
                <h1>Authentification @La Plateforme_</h1>
            </section>

            <section id="signin-buton" class="col-12 col-sm-12 col-md-8">
                <p id="signin-message"></p>
                <div class="g-signin2" data-onsuccess="onSignIn"></div>
            </section>
            <button id="signout">signout</button>
        </div>
    </body>
</html>