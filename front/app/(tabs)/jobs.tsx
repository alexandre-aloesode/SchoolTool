import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import JobsInProgress from '@/components/jobs/JobsInProgress';
import JobsDone from '@/components/jobs/JobsDone';

const JobsMain = () => {
  const [activeTab, setActiveTab] = useState('inProgress');

  const renderComponent = () => {
    switch (activeTab) {
      case 'inProgress':
        return <JobsInProgress />;
      case 'done':
        return <JobsDone />;
      default:
        return <JobsInProgress />;
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.tabContainer}>
        <TouchableOpacity style={[styles.tab, activeTab === 'inProgress' && styles.activeTab]} onPress={() => setActiveTab('inProgress')}>
          <Text style={styles.tabText}>Jobs en cours</Text>
        </TouchableOpacity>
        <TouchableOpacity style={[styles.tab, activeTab === 'done' && styles.activeTab]} onPress={() => setActiveTab('done')}>
          <Text style={styles.tabText}>Jobs terminés</Text>
        </TouchableOpacity>
      </View>
      {renderComponent()}
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: 20, backgroundColor: 'white' },
  tabContainer: { flexDirection: 'row', justifyContent: 'center', marginBottom: 10 },
  tab: { padding: 10, marginHorizontal: 10, borderBottomWidth: 2, borderBottomColor: 'transparent' },
  activeTab: { borderBottomColor: 'red' },
  tabText: { fontSize: 16, fontWeight: 'bold', color: 'black' },
});

export default JobsMain;
