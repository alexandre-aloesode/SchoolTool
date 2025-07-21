import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, SafeAreaView } from 'react-native';
import JobsInProgress from '@/components/jobs/JobsInProgress';
import JobsDone from '@/components/jobs/JobsDone';
import JobsAvailable from '@/components/jobs/JobsAvailable';
import Header from '@/components/global/Header';

const JobsMain = () => {
  const [activeTab, setActiveTab] = useState('inProgress');

  const renderComponent = () => {
    switch (activeTab) {
      case 'inProgress':
        return <JobsInProgress />;
      case 'done':
        return <JobsDone />;
      case 'available':
        return <JobsAvailable />;
      default:
        return <JobsInProgress />;
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <Header />
      <View style={styles.container}>
        <View style={styles.tabContainer}>
          <TouchableOpacity
            style={[styles.tab, activeTab === 'inProgress' && styles.activeTab]}
            onPress={() => setActiveTab('inProgress')}
          >
            <Text style={styles.tabText}>En cours</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.tab, activeTab === 'available' && styles.activeTab]}
            onPress={() => setActiveTab('available')}
          >
            <Text style={styles.tabText}>Disponibles</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.tab, activeTab === 'done' && styles.activeTab]}
            onPress={() => setActiveTab('done')}
          >
            <Text style={styles.tabText}>Terminés</Text>
          </TouchableOpacity>
        </View>

        {renderComponent()}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#f7f7f7',
  },
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: 'white',
  },
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
  activeTab: {
    borderBottomColor: '#e91e63',
  },
  tabText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: 'black',
  },
});

export default JobsMain;
