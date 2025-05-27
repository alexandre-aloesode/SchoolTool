import React, { useEffect, useContext, useState } from 'react';
import { StyleSheet, View, Text, Button, Alert, Platform } from 'react-native';
import * as AuthSession from 'expo-auth-session';
import * as WebBrowser from 'expo-web-browser';
import { ENV } from '@/utils/env';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import AuthContext from '@/context/authContext'; // Import du contexte d'auth

WebBrowser.maybeCompleteAuthSession();

const androidClientId = ENV.ANDROID_CLIENT_ID;
const iosClientId = ENV.IOS_CLIENT_ID;
const webClientId = ENV.LPTF_GOOGLE_CLIENT_ID;
const googleSecret = ENV.GOOGLE_CLIENT_SECRET;
const authUrl = ENV.LPTF_AUTH_API_URL;

export default function LoginWithGoogle() {
  const router = useRouter();
  const { user, setUser } = useContext(AuthContext); // Récupération du contexte d'auth
  const [loading, setLoading] = useState(true);

  const redirectUri = AuthSession.makeRedirectUri({ useProxy: true });
  // const redirectUri = `https://auth.expo.io/@alexaloesode/schooltool`;

  //Prod config
  // const redirectUri = AuthSession.makeRedirectUri({
  //       native: "com.schooltool.authsessiongoogle:/oauthredirect"
  //     }),

  console.log('Redirect URI:', redirectUri);

  const [request, response, promptAsync] = AuthSession.useAuthRequest(
    {
      clientId: Platform.select({
        ios: iosClientId,
        android: webClientId,
        default: webClientId,
      }),
      redirectUri,
      usePKCE: true,
      scopes: [
        'openid',
        'profile',
        'email',
        'https://www.googleapis.com/auth/calendar.readonly',
      ],
      extraParams: {
        access_type: 'offline',
        prompt: 'consent', // pour forcer Google à renvoyer le refresh_token à chaque fois
      },
    },
    { authorizationEndpoint: 'https://accounts.google.com/o/oauth2/v2/auth' },
  );

  useEffect(() => {
    // Vérifier si un utilisateur est déjà connecté
    const checkUserSession = async () => {
      const userData = await AsyncStorage.getItem('userSession');
      if (userData) {
        setUser(JSON.parse(userData)); // Met à jour le contexte utilisateur
      }
      setLoading(false);
    };

    checkUserSession();
  }, []);

  const exchangeCodeForToken = async (code) => {
    try {
      const response = await fetch('https://oauth2.googleapis.com/token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          code,

          client_secret: Platform.OS === 'web' ? googleSecret : undefined,
          client_id: Platform.select({
            ios: iosClientId,
            android: webClientId,
            default: webClientId,
          }),

          // redirect_uri: AuthSession.makeRedirectUri({
          //   scheme: "com.schooltool.authsessiongoogle",
          // }),
          // redirect_uri: `https://auth.expo.io/@alexaloesode/schooltool`,
          redirect_uri: redirectUri,

          grant_type: 'authorization_code',
          code_verifier: request?.codeVerifier,
        }),
      });

      const tokenData = await response.json();

      if (tokenData.access_token) {
        const authToken = await fetch(`${authUrl}/oauth`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `token_id=${tokenData.id_token}`,
        });

        let apiToken = await authToken.json();

        const userSession = {
          accessToken: apiToken.token,
          authToken: apiToken.authtoken,
          googleAccessToken: tokenData.access_token,
          googleExpiresIn: tokenData.expires_in,
          googleScope: tokenData.scope,
          googleRefreshToken: tokenData.refresh_token,
        };

        await AsyncStorage.setItem('userSession', JSON.stringify(userSession));
        setUser(userSession);

        router.replace('/');
      } else {
        console.log(
          'Erreur lors de la récupération du token Google :',
          tokenData,
        );
        Alert.alert('Erreur', "Impossible d'obtenir un jeton d'accès");
      }
    } catch (error) {
      console.error(
        "Erreur lors de l'échange du code contre un jeton :",
        error,
      );
      Alert.alert(
        'Erreur',
        "Problème lors de l'échange du code d'autorisation",
      );
    }
  };

  useEffect(() => {
    if (response?.type === 'success' && response.params?.code) {
      const { code } = response.params;
      exchangeCodeForToken(code);
    }
  }, [response]);

  if (loading) {
    return <Text>Chargement...</Text>;
  }

  return (
    <View style={styles.container}>
      {user ? (
        <Text style={styles.title}>Bienvenue, vous êtes connecté !</Text>
      ) : (
        <>
          <Text style={styles.title}>Bienvenue sur l'application</Text>
          <Button
            disabled={!request}
            title="Se connecter avec Google"
            onPress={() => promptAsync({ useProxy: true })}
            color="#4285F4"
          />
        </>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f7f7f7',
  },
  title: {
    fontSize: 24,
    marginBottom: 20,
    color: '#333',
  },
});
