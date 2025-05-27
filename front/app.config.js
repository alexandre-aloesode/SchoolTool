import 'dotenv/config';

export default () => ({
  expo: {
    name: 'SchoolTool',
    slug: 'schooltool',
    version: '1.0.0',
    orientation: 'portrait',
    scheme: 'com.schooltool.authsessiongoogle',
    icon: './assets/images/icon.png',
    userInterfaceStyle: 'automatic',
    newArchEnabled: true,

    // 📱 Android config
    android: {
      package: 'com.schooltool.authsessiongoogle',
      adaptiveIcon: {
        foregroundImage: './assets/images/adaptive-icon.png',
        backgroundColor: '#ffffff',
      },
    },

    // 🍎 iOS config
    ios: {
      bundleIdentifier: 'com.schooltool.authsessiongoogle',
      supportsTablet: true,
    },

    // 🌐 Web config
    web: {
      bundler: 'metro',
      output: 'static',
      favicon: './assets/images/favicon.png',
    },

    // 🔌 Plugins utilisés
    plugins: [
      'expo-router',
      [
        'expo-splash-screen',
        {
          image: './assets/images/splash-icon.png',
          imageWidth: 200,
          resizeMode: 'contain',
          backgroundColor: '#ffffff',
        },
      ],
      'expo-secure-store',
    ],

    // 🧪 Fonctionnalités expérimentales
    experiments: {
      typedRoutes: true,
    },

    // 🔐 Variables d'environnement injectées depuis .env
    extra: {
      ANDROID_CLIENT_ID: process.env.ANDROID_CLIENT_ID,
      IOS_CLIENT_ID: process.env.IOS_CLIENT_ID,
      WEB_CLIENT_ID: process.env.WEB_CLIENT_ID,
      LPTF_GOOGLE_CLIENT_ID: process.env.LPTF_GOOGLE_CLIENT_ID,
      GOOGLE_CLIENT_SECRET: process.env.GOOGLE_CLIENT_SECRET,
      LPTF_API_URL: process.env.LPTF_API_URL,
      LPTF_AUTH_API_URL: process.env.LPTF_AUTH_API_URL,
      // 📦 EAS Project ID (obligatoire pour les builds)
      eas: {
        projectId: 'e427c0d9-fe3e-4038-a31e-f4a072fc7a79',
      },
    },
  },
});
