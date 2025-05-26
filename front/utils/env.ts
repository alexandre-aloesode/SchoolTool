import Constants from "expo-constants";

// Supporte Expo Go / Web / EAS Build
const extra = Constants?.manifest?.extra || Constants?.expoConfig?.extra;

if (!extra) {
  throw new Error("❌ Impossible de charger les variables d'environnement depuis app.config.js");
}

export const ENV = {
  ANDROID_CLIENT_ID: extra.ANDROID_CLIENT_ID,
  IOS_CLIENT_ID: extra.IOS_CLIENT_ID,
  WEB_CLIENT_ID: extra.WEB_CLIENT_ID,
  LPTF_GOOGLE_CLIENT_ID: extra.LPTF_GOOGLE_CLIENT_ID,
  GOOGLE_CLIENT_SECRET: extra.GOOGLE_CLIENT_SECRET,
  LPTF_API_URL: extra.LPTF_API_URL,
  LPTF_AUTH_API_URL: extra.LPTF_AUTH_API_URL,
};
