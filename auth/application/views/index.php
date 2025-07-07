<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login Google</title>
    <meta name="google-signin-client_id" content="462034163728-n05qp98g4t5sjjcovkvtmt4vkflveipn.apps.googleusercontent.com">
    <!-- <script src="https://accounts.google.com/gsi/client" async defer></script> -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        body {
            background-color: #88c1d0;
            color: white;
            text-align: center;
            padding: 50px;
        }

        #signout {
            display: none;
        }
    </style>
</head>

<body>
    <h1>Authentification @La Plateforme_</h1>
    <div id="g_id_onload"
        data-client_id="462034163728-n05qp98g4t5sjjcovkvtmt4vkflveipn.apps.googleusercontent.com"
        data-callback="handleCredentialResponse"
        data-auto_prompt="false">
    </div>
    <div class="g_id_signin" data-type="standard"></div>

    <p id="signin-message"></p>
    <button id="signout">Sign out</button>

    <script>
        function handleCredentialResponse(response) {
            const id_token = response.credential;
            console.log("id_token:", id_token);

            $.ajax({
                url: 'http://localhost:8001/oauth',
                //   url: OAUTH_URL,
                type: 'POST',
                data: {
                    token_id: id_token
                },
                success: function(res) {
                    console.log("Auth OK:", res);
                    window.ReactNativeWebView?.postMessage(JSON.stringify({
                        token: res
                    }));
                    $("#signin-message").text("Connexion réussie !");
                    $("#signout").show();
                },
                error: function(err) {
                    console.log("Auth error:", err);
                }
            });
        }

        document.getElementById("signout").onclick = function() {
            google.accounts.id.disableAutoSelect();
            $("#signin-message").text("");
            $("#signout").hide();
            location.reload(); // ou autre logique de déconnexion
        };
    </script>
</body>

</html>