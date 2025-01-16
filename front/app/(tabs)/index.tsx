import React, { useEffect } from "react";
import { StyleSheet, View, Text, Button, Alert, Platform } from "react-native";
import * as AuthSession from "expo-auth-session";
import * as WebBrowser from "expo-web-browser";
import config from "../../config.js";

WebBrowser.maybeCompleteAuthSession();

const androidClientId = config.ANDROID_CLIENT_ID;
const iosClientId = config.IOS_CLIENT_ID;
const webClientId = config.WEB_CLIENT_ID;
const googleSecret = config.GOOGLE_CLIENT_SECRET;

console.log("androidClientId", androidClientId);

export default function HomeScreen() {
  const [request, response, promptAsync] = AuthSession.useAuthRequest(
    {
      clientId: Platform.select({
        ios: iosClientId,
        android: androidClientId,
        default: webClientId,
      }),
      redirectUri: AuthSession.makeRedirectUri({
        scheme: "com.schooltool.authsessiongoogle",
      }),
      scopes: ["openid", "profile", "email"],
    },
    { authorizationEndpoint: "https://accounts.google.com/o/oauth2/v2/auth" }
  );

  const exchangeCodeForToken = async (code: string) => {
    console.log("code", code);
    
    try {
      console.log(
        "redirect adress",
        AuthSession.makeRedirectUri({
          scheme: "com.schooltool.authsessiongoogle",
        })
      );

      const response = await fetch("https://oauth2.googleapis.com/token", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          code,
          client_secret: Platform.OS === "web" ? googleSecret : undefined,
          client_id: Platform.select({ ios: iosClientId, android: androidClientId, default: webClientId }),
          redirect_uri: AuthSession.makeRedirectUri({
            scheme: "com.schooltool.authsessiongoogle",
          }),
          grant_type: "authorization_code",
        }),
      });

      const tokenData = await response.json();

      if (tokenData.access_token) {
        fetchUserData(tokenData.access_token);
      } else {
        console.log("Erreur lors de la récupération du token Google :", tokenData);
        Alert.alert("Erreur", "Impossible d'obtenir un jeton d'accès");
      }
    } catch (error) {
      console.error("Erreur lors de l'échange du code contre un jeton :", error);
      Alert.alert("Erreur", "Problème lors de l'échange du code d'autorisation");
    }
  };

  const fetchUserData = async (accessToken: string) => {
    try {
      const userInfoResponse = await fetch("https://www.googleapis.com/oauth2/v2/userinfo", {
        headers: { Authorization: `Bearer ${accessToken}` },
      });
      const userInfo = await userInfoResponse.json();
      Alert.alert("Connecté", `Bienvenue ${userInfo.name}`);
    } catch (error) {
      Alert.alert("Erreur", "Impossible de récupérer les informations utilisateur");
      console.error(error);
    }
  };
  
  useEffect(() => {
    console.log("Réponse AuthSession :", response);
    if (response?.type === "success" && response.params?.code) {
      const { code } = response.params;
      exchangeCodeForToken(code);
    }
  }, [response]);

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Bienvenue sur l'application</Text>
      <Button disabled={!request} title="Se connecter avec Google" onPress={() => promptAsync()} color="#4285F4" />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "#f7f7f7",
  },
  title: {
    fontSize: 24,
    marginBottom: 20,
    color: "#333",
  },
});
