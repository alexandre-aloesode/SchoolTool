import React from 'react';
import {
  StyleSheet,
  View,
  ScrollView,
  SafeAreaView,
  useWindowDimensions,
} from 'react-native';
import LoginWithGoogle from '@/components/auth/GoogleAuth';
import LogtimeChart from '@/components/dashboard/logtimes';
import GoogleCalendarWidget from '@/components/dashboard/googleCalendar';
import Header from '@/components/global/Header';
import { useAuth } from '@/hooks/useAuth';

export default function HomeScreen() {
  const { user } = useAuth();
  const { height } = useWindowDimensions();

  if (!user) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.loginWrapper}>
          <LoginWithGoogle />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <Header />
      <ScrollView
        contentContainerStyle={[styles.scrollContent, { minHeight: height }]}
        bounces={false}
      >
        <View style={styles.chartSection}>
          <LogtimeChart />
        </View>
        <View style={styles.calendarSection}>
          <GoogleCalendarWidget />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#f7f7f7',
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'flex-start',
    alignItems: 'center',
    paddingVertical: 16,
  },
  chartSection: {
    width: '92%',
    minHeight: 320,
    marginBottom: 12,
  },
  calendarSection: {
    width: '92%',
    minHeight: 320,
  },
  loginWrapper: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
