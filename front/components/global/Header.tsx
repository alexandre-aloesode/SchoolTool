import React from 'react';
import { View, Text, StyleSheet, Image } from 'react-native';
import { useAuth } from '@/hooks/useAuth';
import { usePathname } from 'expo-router';

const Header = () => {
  const { user } = useAuth();
  const pathname = usePathname();
  const isHomePage = pathname === '/';

  const getNameFromEmail = (email: string) => {
    const [fullName] = email.split('@');
    const [first, last] = fullName.split('.');
    const capitalize = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);

    return `${capitalize(first)} ${capitalize(last)}`;
  };

  return (
    <View style={styles.container}>
      <Image
        source={require('@/assets/images/logo.png')}
        style={styles.logo}
        resizeMode="contain"
      />
      {user && isHomePage && (
        <View style={styles.welcomeContainer}>
          <Text style={styles.welcome}>Bienvenue</Text>
          <Text style={styles.name}>{getNameFromEmail(user.email)}</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingTop: 20,
    paddingBottom: 10,
    paddingHorizontal: 16,
    backgroundColor: '#fff',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderColor: '#e6e6e6',
  },
  logo: {
    height: 40,
    marginBottom: 6,
  },
  welcomeContainer: {
    alignItems: 'center',
  },
  welcome: {
    fontWeight: '600',
    fontSize: 16,
  },
  name: {
    fontWeight: 'bold',
    fontSize: 16,
  },
});

export default Header;
