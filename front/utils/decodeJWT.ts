import { DecodedJWT } from '@/types/decodedJWTTypes';

export function decodeJWT(token: string): DecodedJWT | null {
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
      atob(base64)
        .split('')
        .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
        .join(''),
    );

    return JSON.parse(jsonPayload);
  } catch (e) {
    console.error('Erreur lors du décodage du JWT :', e);
    return null;
  }
}
