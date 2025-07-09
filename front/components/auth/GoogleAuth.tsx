import React, { useEffect, useState } from 'react';
import { StyleSheet, View, Text, Button, Alert, Platform } from 'react-native';
import * as AuthSession from 'expo-auth-session';
import * as WebBrowser from 'expo-web-browser';
import { ENV } from '@/utils/env';
import { useRouter } from 'expo-router';
import Constants from 'expo-constants';
import Toast from 'react-native-toast-message';
import { Session } from '@/utils/session';
import { decodeJWT } from '@/utils/decodeJWT';
import { useAuth } from '@/hooks/useAuth';

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
  const { user, setUser } = useAuth();
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
      const storedUser = await Session.getUserData();
      if (storedUser) {
        setUser(storedUser);
      }
      setLoading(false);
    };

    checkUserSession();
  }, []);

  const exchangeCodeForToken = async (code: string) => {
    try {
      const response = await fetch('https://oauth2.googleapis.com/token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          code,
          client_id: googleClientId,
          client_secret: isExpoGo ? undefined : googleSecret,
          redirect_uri: redirectUri,
          grant_type: 'authorization_code',
          code_verifier: request?.codeVerifier || '',
        }).toString(),
      });

      const tokenData = await response.json();

      if (!tokenData.access_token) {
        console.log('Token response:', tokenData);
        Alert.alert('Erreur', 'Token Google non reçu');
        return;
      }

      const authToken = await fetch(`${authUrl}/oauth`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `token_id=${tokenData.id_token}`,
      });

      const apiToken = await authToken.json();

      const decoded = decodeJWT(apiToken.token);
      if (!decoded) {
        Alert.alert('Erreur', 'Impossible de décoder le token utilisateur');
        return;
      }
      console.log(tokenData);
      

      const userSession = {
        accessToken: apiToken.token,
        authToken: apiToken.authtoken,
        googleAccessToken: tokenData.access_token,
        googleExpiresIn: tokenData.expires_in,
        googleExpiresAt: Date.now() + tokenData.expires_in * 1000,
        googleScope: tokenData.scope,
        googleRefreshToken: tokenData.refresh_token,
      };

      const userData = {
        id: decoded.user_id,
        email: decoded.user_email,
        role: decoded.role,
        scope: decoded.scope,
      };

      await Session.set(userSession, userData);
      setUser(userData);

      Toast.show({
        type: 'success',
        text1: 'Connexion réussie',
        text2: 'Vous êtes maintenant connecté avec Google',
      });

      router.replace('/');
    } catch (error) {
      console.error("Erreur lors de l'authentification :", error);
      Alert.alert('Erreur', 'Une erreur est survenue pendant la connexion.');
    }
  };

  useEffect(() => {
    if (response?.type === 'success' && response.params?.code) {
      const { code } = response.params;
      exchangeCodeForToken(code);
    } else if (response?.type === 'error') {
      console.error('OAuth error:', response.error);
    } else {
      console.log('OAuth annulé ou réponse inconnue');
    }
  }, [response]);

  if (loading) {
    return <Text>Chargement...</Text>;
  }

  return (
    <View style={styles.container}>
      {user ? (
        <Text style={styles.title}>Bienvenue, {user.email} !</Text>
      ) : (
        <>
          <Text style={styles.title}>Bienvenue sur l'application</Text>
          <Button
            disabled={!request}
            title="Se connecter avec Google"
            onPress={() => promptAsync({ useProxy })}
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
