import React, { useState } from 'react';
import { View, Text, FlatList, StyleSheet } from 'react-native';
import { ProgressBar, IconButton } from 'react-native-paper';

const JobsDone = () => {
  const jobsInProgress = [
    { job_id: 3, job_name: 'Financeflow', start_date: '2024-02-01', end_date: '2024-02-10' },
    { job_id: 4, job_name: 'Safebase', start_date: '2024-02-05', end_date: '2024-02-15' }
  ];

  const getProgress = (job) => {
    let startDate = new Date(job.start_date);
    let endDate = new Date(job.end_date);
    let today = new Date();

    let duration = (endDate - startDate) / (1000 * 60 * 60 * 24);
    let remaining = (endDate - today) / (1000 * 60 * 60 * 24);
    return Math.max(0, (duration - remaining) / duration);
  };

  const renderJob = ({ item }) => (
    <View style={styles.row}>
      <Text style={styles.jobTitle}>{item.job_name}</Text>
      <ProgressBar progress={getProgress(item)} color={'#0097a7'} style={styles.progress} />
      <IconButton icon="clock-outline" size={20} onPress={() => {}} />
      <IconButton icon="magnify" size={20} onPress={() => {}} />
    </View>
  );

  return (
    <View style={styles.container}>
      <Text style={styles.header}>Projets terminés</Text>
      <FlatList data={jobsInProgress} renderItem={renderJob} keyExtractor={(item) => item.job_id.toString()} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: 20, backgroundColor: 'white' },
  header: { fontSize: 20, fontWeight: 'bold', marginBottom: 10, color: 'red' },
  row: { flexDirection: 'row', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#ddd' },
  jobTitle: { flex: 1, fontSize: 16, fontWeight: 'bold' },
  progress: { flex: 2, height: 8, borderRadius: 4, backgroundColor: '#ddd', marginHorizontal: 10 },
});

export default JobsDone;
