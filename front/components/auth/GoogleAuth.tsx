import React, { useEffect, useContext, useState } from 'react';
import { StyleSheet, View, Text, Button, Alert, Platform } from 'react-native';
import * as AuthSession from 'expo-auth-session';
import * as WebBrowser from 'expo-web-browser';
import { ENV } from '@/utils/env';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import AuthContext from '@/context/authContext';
import Constants from 'expo-constants';
import Toast from 'react-native-toast-message';

WebBrowser.maybeCompleteAuthSession();

const isWeb = Platform.OS === 'web';
const isAndroid = Platform.OS === 'android';
const isExpoGo = Constants.executionEnvironment === 'storeClient';

const androidClientId = isExpoGo
  ? ENV.ANDROID_CLIENT_ID_EXPOGO
  : ENV.ANDROID_CLIENT_ID;
const iosClientId = ENV.IOS_CLIENT_ID;
const webClientId = ENV.LPTF_GOOGLE_CLIENT_ID;
const googleSecret = isExpoGo
  ? ENV.EXPO_GO_GOOGLE_CLIENT_SECRET
  : ENV.GOOGLE_CLIENT_SECRET;
const authUrl = ENV.LPTF_AUTH_API_URL;

export default function LoginWithGoogle() {
  const router = useRouter();
  const { user, setUser } = useContext(AuthContext);
  const [loading, setLoading] = useState(true);

  const useProxy = isWeb || isExpoGo;

  const redirectUri = isExpoGo
    ? 'https://auth.expo.io/@alexaloesode/schooltool'
    : AuthSession.makeRedirectUri({ useProxy: true });

  const googleClientId = isExpoGo
    ? ENV.ANDROID_CLIENT_ID_EXPOGO
    : isWeb
      ? webClientId
      : isAndroid
        ? androidClientId
        : iosClientId;

  const [request, response, promptAsync] = AuthSession.useAuthRequest(
    {
      clientId: googleClientId,
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
        prompt: 'consent',
        response_type: 'code',
      },
    },
    { authorizationEndpoint: 'https://accounts.google.com/o/oauth2/v2/auth' },
  );

  useEffect(() => {
    const checkUserSession = async () => {
      const userData = await AsyncStorage.getItem('userSession');
      if (userData) {
        setUser(JSON.parse(userData));
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
          client_id: googleClientId,
          // client_secret: useProxy ? googleSecret : '',
          client_secret: isExpoGo ? undefined : googleSecret,
          redirect_uri: redirectUri,
          grant_type: 'authorization_code',
          code_verifier: request?.codeVerifier,
        }),
      });
      const tokenData = await response.json();

      if (!tokenData.access_token) {
        console.log('Token response:', tokenData);
      }
      if (tokenData.access_token) {
        const authToken = await fetch(`${authUrl}/oauth`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `token_id=${tokenData.id_token}`,
        });

        const apiToken = await authToken.json();

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
        Toast.show({
          type: 'success',
          text1: 'Connexion réussie',
          text2: 'Vous êtes maintenant connecté avec Google',
        });
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
    } else if (response?.type === 'error') {
      console.error('OAuth error:', response.error);
    } else {
      console.log('OAuth cancelled or unknown response');
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
            onPress={() => promptAsync({ useProxy })}
            // onPress={() => promptAsync()}
            // onPress={async () => {
            //   console.log('Prompting auth...');
            //   const result = await promptAsync({ useProxy });
            //   console.log('PromptAsync result:', result);
            // }}
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
