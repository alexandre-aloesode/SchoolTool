import React from 'react';
import { StyleSheet, View, Text, Button } from 'react-native';
import LoginWithGoogle from '@/components/auth/GoogleAuth';
import LogtimeChart from '@/components/dashboard/logtimes';
import GoogleCalendarWidget from '@/components/dashboard/googleCalendar';
import { useAuth } from '@/hooks/useAuth';
// import GoogleLoginWebView from '@/components/auth/GoogleLoginWebView';

export default function HomeScreen() {
  const { user, logout } = useAuth();

  return (
    <View style={styles.container}>
      {user ? (
        <>
          <LogtimeChart />
          <GoogleCalendarWidget />
        </>
      ) : (
        <LoginWithGoogle />
        // <GoogleLoginWebView />
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
    paddingTop: 20,
    paddingBottom: 60,
  },
  title: {
    fontSize: 24,
    marginBottom: 20,
    color: '#333',
  },
});
