import { Tabs } from 'expo-router';
import React, { useContext } from 'react';
import { Platform } from 'react-native';

import { HapticTab } from '@/components/HapticTab';
import { IconSymbol } from '@/components/ui/IconSymbol';
import TabBarBackground from '@/components/ui/TabBarBackground';
import { Colors } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useColorScheme';
import AuthContext from '@/context/authContext';

export default function TabLayout() {
  const colorScheme = useColorScheme();
  const auth = useContext(AuthContext);
  // console.log("auth", auth.user);
  // console.log("authtype", typeof(auth.user));

  return (
    <Tabs
      screenOptions={{
        tabBarActiveTintColor: Colors[colorScheme ?? 'light'].tint,
        headerShown: false,
        tabBarButton: HapticTab,
        tabBarBackground: TabBarBackground,
        tabBarStyle: Platform.select({
          ios: {
            // Use a transparent background on iOS to show the blur effect
            position: 'absolute',
            backgroundColor: '#0084FA',
          },
          default: {
            paddingTop: 4,
            backgroundColor: '#0084FA',
          },
        }),
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: '',
          tabBarIcon: ({ color }) => (
            <IconSymbol size={28} name="house.fill" color="white" />
          ),
        }}
      />
      <Tabs.Screen
        name="jobs"
        options={{
          title: '',
          tabBarIcon: ({ color }) => (
            <IconSymbol size={28} name="briefcase.fill" color="white" />
          ),
        }}
      />
      <Tabs.Screen
        name="absences"
        options={{
          title: '',
          tabBarIcon: ({ color }) => (
            <IconSymbol
              size={28}
              name="person.crop.circle.badge.xmark"
              color="white"
            />
          ),
        }}
      />
      <Tabs.Screen
        name="calendar"
        options={{
          title: '',
          tabBarIcon: ({ color }) => (
            <IconSymbol size={28} name="calendar.badge.clock" color="white" />
          ),
        }}
      />
    </Tabs>
  );
}
