import { Tabs } from 'expo-router';
import React, { useContext } from 'react';
import { Platform } from 'react-native';
import { HapticTab } from '@/components/HapticTab';
import { IconSymbol } from '@/components/ui/IconSymbol';
import TabBarBackground from '@/components/ui/TabBarBackground';
import { Colors } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useColorScheme';
import { useAuth } from '@/hooks/useAuth';

export default function TabLayout() {
  const colorScheme = useColorScheme();
  const { user } = useAuth();

  if (!user) {
    return (
      <Tabs
        screenOptions={{
          tabBarActiveTintColor: Colors[colorScheme ?? 'light'].tint,
          headerShown: false,
          tabBarButton: HapticTab,
          tabBarBackground: TabBarBackground,
          tabBarStyle: { display: 'none' }
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
      </Tabs>
    );
  }

  return (
    <Tabs
      screenOptions={{
        tabBarActiveTintColor: Colors[colorScheme ?? 'light'].tint,
        headerShown: false,
        tabBarButton: HapticTab,
        tabBarBackground: TabBarBackground,
        tabBarStyle: Platform.select({
          ios: {
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
            <IconSymbol size={28} name="calendar.badge.minus" color="white" />
          ),
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: '',
          tabBarIcon: ({ color }) => (
            <IconSymbol size={28} name="person.crop.circle" color="white" />
          ),
        }}
      />
    </Tabs>
  );
}
