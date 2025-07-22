import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  SafeAreaView,
  ScrollView,
  useWindowDimensions,
} from 'react-native';
import ProfileScreen from '@/components/profile/StudentProfile';
import SkillScreen from '@/components/profile/StudentSkills';
import Header from '@/components/global/Header';

const ProfileMain = () => {
  const [activeTab, setActiveTab] = useState('profile');
  const { height } = useWindowDimensions();

  const renderComponent = () => {
    switch (activeTab) {
      case 'profile':
        return <ProfileScreen />;
      case 'skills':
        return <SkillScreen />;
      default:
        return <ProfileScreen />;
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <Header />
      <View style={styles.tabContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'profile' && styles.activeTab]}
          onPress={() => setActiveTab('profile')}
        >
          <Text style={styles.tabText}>Profil</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'skills' && styles.activeTab]}
          onPress={() => setActiveTab('skills')}
        >
          <Text style={styles.tabText}>Compétences</Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        contentContainerStyle={[styles.content, { minHeight: height }]}
        bounces={false}
      >
        {renderComponent()}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#fff',
  },
  tabContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    paddingVertical: 12,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#ddd',
  },
  tab: {
    paddingBottom: 10,
    marginHorizontal: 16,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  activeTab: {
    borderBottomColor: '#0084FA',
  },
  tabText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  content: {
    flexGrow: 1,
  },
});

export default ProfileMain;
