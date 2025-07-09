import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import ProfileScreen from '@/components/profile/StudentProfile';
import SkillScreen from '@/components/profile/StudentSkills';

const ProfileMain = () => {
  const [activeTab, setActiveTab] = useState('profile');

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
    <View style={styles.container}>
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
      {renderComponent()}
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: 20, backgroundColor: 'white' },
  tabContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginBottom: 10,
  },
  tab: {
    padding: 10,
    marginHorizontal: 10,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  activeTab: { borderBottomColor: 'red' },
  tabText: { fontSize: 16, fontWeight: 'bold', color: 'black' },
});

export default ProfileMain;
