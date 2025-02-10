import AsyncStorage from "@react-native-async-storage/async-storage";
import * as AuthSession from "expo-auth-session";
import * as WebBrowser from "expo-web-browser";
import config from "../config.js";
import React, { useEffect } from "react";
import { StyleSheet, View, Text, Button, Alert, Platform } from "react-native";

WebBrowser.maybeCompleteAuthSession();

const androidClientId = config.ANDROID_CLIENT_ID;
const iosClientId = config.IOS_CLIENT_ID;
const webClientId = config.LPTF_GOOGLE_CLIENT_ID;
const googleSecret = config.GOOGLE_CLIENT_SECRET;

export const AuthActions = {
  async loginWithGoogle() {
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
        usePKCE: true,
        scopes: ["openid", "profile", "email"],
      },
      { authorizationEndpoint: "https://accounts.google.com/o/oauth2/v2/auth" }
    );

    try {
      await promptAsync();
      if (response?.type === "success" && response.params?.code) {
        const { code } = response.params;
        const tokenResponse = await fetch("https://oauth2.googleapis.com/token", {
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
            code_verifier: request?.codeVerifier,
          }),
        });

        const tokenData = await tokenResponse.json();
        if (tokenData.access_token) {
          await generateAuthToken(tokenData.id_token);
        } else {
          Alert.alert("Erreur", "Impossible d'obtenir un jeton d'accès");
        }
      }
    } catch (error) {
      Alert.alert("Erreur", "Problème lors de la connexion avec Google");
    }
  },

  async generateAuthToken(id_token) {
    try {
      let response = await fetch("http://localhost:8082/oauth", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `token_id=${id_token}`,
      });

      let token = await response.json();
      if (token.hasOwnProperty("token")) {
        await AsyncStorage.setItem("token", token.token);
        let decodedToken = getTokenPayload(token.token);
        await AsyncStorage.setItem("role", decodedToken.role);
        await AsyncStorage.setItem("email", decodedToken.user_email);
        console.log("decodedToken", decodedToken);
      }

      if (token.hasOwnProperty("authtoken")) {
        await AsyncStorage.setItem("authtoken", token.authtoken);
      } else {
        Alert.alert("Erreur", "Vous n'êtes pas autorisé à utiliser l'application");
      }
    } catch (error) {
      console.error("Erreur lors de l'authentification", error);
    }
  },

  async getTokenPayload(token) {
    if (!token) return null;
    try {
      let base64Url = token.split(".")[1];
      let base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
      let decodedPayload = JSON.parse(
        decodeURIComponent(
          atob(base64)
            .split("")
            .map((c) => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2))
            .join("")
        )
      );
      return decodedPayload;
    } catch (error) {
      console.error("Erreur lors du décodage du token", error);
      return null;
    }
  },
};
export default AuthActions;
