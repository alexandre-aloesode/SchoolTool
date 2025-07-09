import { Session } from '@/utils/session';
import { ENV } from '@/utils/env';

export const getValidGoogleAccessToken = async (): Promise<string | null> => {
  try {
    const session = await Session.getSession();
    if (!session) return null;

    const { googleAccessToken, googleRefreshToken, googleExpiresAt } = session;
    const now = Date.now();

    if (googleAccessToken && googleExpiresAt && now < googleExpiresAt) {
      return googleAccessToken;
    }

    if (!googleRefreshToken) {
      console.warn('Aucun refresh token disponible.');
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
      console.error('Erreur lors du rafraîchissement du token :', tokenData);
      return null;
    }

    const updatedSession = {
      ...session,
      googleAccessToken: tokenData.access_token,
      googleExpiresAt: Date.now() + tokenData.expires_in * 1000,
    };

    await Session.updateSessionOnly(updatedSession);

    return tokenData.access_token;
  } catch (error) {
    console.error('Erreur dans getValidGoogleAccessToken :', error);
    return null;
  }
};
