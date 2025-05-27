// utils/googleToken.ts
import AsyncStorage from '@react-native-async-storage/async-storage';
import { ENV } from '@/utils/env';

export const getValidGoogleAccessToken = async (): Promise<string | null> => {
  try {
    const session = await AsyncStorage.getItem('userSession');
    if (!session) return null;

    const { googleAccessToken, googleRefreshToken, googleExpiresAt } =
      JSON.parse(session);

    const now = Date.now();

    if (googleAccessToken && googleExpiresAt && now < googleExpiresAt) {
      // Token is still valid
      return googleAccessToken;
    }

    if (!googleRefreshToken) {
      console.warn('Pas de refresh token disponible.');
      return null;
    }

    const refreshResponse = await fetch('https://oauth2.googleapis.com/token', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        client_id: ENV.LPTF_GOOGLE_CLIENT_ID,
        client_secret: ENV.GOOGLE_CLIENT_SECRET,
        grant_type: 'refresh_token',
        refresh_token: googleRefreshToken,
      }).toString(),
    });

    const tokenData = await refreshResponse.json();

    if (!tokenData.access_token) {
      console.error('Erreur de refresh du token:', tokenData);
      return null;
    }

    const updatedSession = {
      ...JSON.parse(session),
      googleAccessToken: tokenData.access_token,
      googleExpiresAt: Date.now() + tokenData.expires_in * 1000,
    };

    await AsyncStorage.setItem('userSession', JSON.stringify(updatedSession));

    return tokenData.access_token;
  } catch (err) {
    console.error('Erreur dans getValidGoogleAccessToken:', err);
    return null;
  }
};
