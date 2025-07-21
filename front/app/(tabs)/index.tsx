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
import { useAuth } from '@/hooks/useAuth';

export default function HomeScreen() {
  const { user } = useAuth();
  const { height } = useWindowDimensions();

  return (
    <SafeAreaView style={styles.safeArea}>
      {user ? (
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
      ) : (
        <View style={styles.loginWrapper}>
          <LoginWithGoogle />
        </View>
      )}
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
    width: '90%',
    height: '40%',
    marginBottom: 12,
  },
  calendarSection: {
    width: '90%',
    height: '40%',
  },
  loginWrapper: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
