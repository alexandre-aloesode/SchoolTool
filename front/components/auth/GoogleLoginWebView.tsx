import React from 'react';
import { View, StyleSheet, ActivityIndicator, Alert } from 'react-native';
import { WebView } from 'react-native-webview';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import AuthContext from '@/context/authContext';

export default function GoogleLoginWebView() {
  const router = useRouter();
  const { setUser } = React.useContext(AuthContext);
  
  const handleWebViewMessage = async (event: any) => {
      try {
          console.log('Rendering WebView to:', 'http://192.168.1.106:8001');
          const data = JSON.parse(event.nativeEvent.data);
            console.log('Données reçues depuis le WebView:', data);
      if (data.token) {
        const userSession = {
          accessToken: data.token.token,
          authToken: data.token.authtoken,
          googleAccessToken: data.token.googleAccessToken, // facultatif
        };

        await AsyncStorage.setItem('userSession', JSON.stringify(userSession));
        setUser(userSession);
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
