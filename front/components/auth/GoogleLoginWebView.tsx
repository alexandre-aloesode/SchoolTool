import React from 'react';
import { View, StyleSheet, ActivityIndicator, Alert } from 'react-native';
import { WebView } from 'react-native-webview';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import { useAuth } from '@/hooks/useAuth';

export default function GoogleLoginWebView() {
  const router = useRouter();
  const { setUser } = useAuth();

  const handleWebViewMessage = async (event: any) => {
    try {
      const data = JSON.parse(event.nativeEvent.data);

      if (data.token) {
        const userSession = {
          accessToken: data.token.token,
          authToken: data.token.authtoken,
          googleAccessToken: data.token.googleAccessToken,
        };

        await AsyncStorage.setItem('userSession', JSON.stringify(userSession));
        // setUser(userSession);const [logtimes, setLogtimes] = useState<Logtime[]>([]);
        router.replace('/');
      } else {
        Alert.alert('Erreur', 'Données reçues invalides depuis le WebView');
      }
    } catch (e) {
      console.error('Erreur WebView → RN', e);
      Alert.alert('Erreur', 'Impossible de traiter les données de connexion');
    }
  };

  return (
    <View style={styles.container}>
      <WebView
        source={{ uri: 'http://192.168.1.106:8001' }}
        onMessage={handleWebViewMessage}
        startInLoadingState
        javaScriptEnabled
        domStorageEnabled
        renderLoading={() => <ActivityIndicator size="large" color="#0000ff" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
