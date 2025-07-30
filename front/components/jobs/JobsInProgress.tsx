import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, StyleSheet, Pressable } from 'react-native';
import { ProgressBar } from 'react-native-paper';
import { ApiActions } from '@/services/ApiServices';
import ProgressModal from './modals/ProgressModal';
import type { JobInProgress } from '@/types/jobsTypes';

const JobsInProgress = () => {
  const [selectedJob, setSelectedJob] = useState<JobInProgress | null>(null);
  const [jobs, setJobs] = useState<JobInProgress[]>([]);

  const getJobsInProgress = async () => {
    const allJobs: JobInProgress[] = [];

    const endpoints = [
      { route: '/job/await', label: 'await' },
      { route: '/job/progress', label: 'progress' },
      { route: '/job/ready', label: 'ready' },
    ];

    for (const { route } of endpoints) {
      const res = await ApiActions.get({
        route,
        params: {
          job_name: '',
          job_id: '',
          group_id: '',
          registration_id: '',
          start_date: '',
          end_date: '',
          order: route === '/job/ready' ? 'click_date' : 'end_date',
          desc: '',
        },
      });

      if (res?.status === 200 && Array.isArray(res.data)) {
        allJobs.push(...res.data);
      }
    }

    setJobs(allJobs);
  };

  useEffect(() => {
    getJobsInProgress();
  }, []);

  const getProgressInfo = (job: JobInProgress) => {
    const startStr = String(job.start_date).replace(' ', 'T');
    const endStr = String(job.end_date).replace(' ', 'T');

    const startDate = new Date(startStr);
    const endDate = new Date(endStr);
    const today = new Date();

    const duration =
      (endDate.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24);
    const remaining =
      (endDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24);
    const delay = Math.max(0, -remaining);
    const progress = Math.min(
      1,
      Math.max(0, (duration - remaining) / duration),
    );

    return { progress, delay: Math.round(delay), isLate: delay > 0 };
  };

  const renderHeader = () => (
    <View style={[styles.row, styles.headerRow]}>
      <Text style={[styles.columnTitle, { flex: 1 }]}>Nom</Text>
      <Text style={[styles.columnTitle, { flex: 3 }]}>Progression</Text>
    </View>
  );

  const renderJob = ({ item }: { item: JobInProgress }): JSX.Element | null => {
    if (!item) return null;
    const { progress, delay, isLate } = getProgressInfo(item);

    return (
      <Pressable style={styles.row} onPress={() => setSelectedJob(item)}>
        <Text style={[styles.jobTitle, { flex: 1 }]}>{item.job_name}</Text>
        <View style={[styles.progressContainer, { flex: 3 }]}>
          <View style={styles.barAndLabel}>
            <ProgressBar
              progress={progress}
              color="#0097a7"
              style={styles.progress}
            />
            {isLate && (
              <Text style={styles.lateLabel}>Retard de {delay} jours</Text>
            )}
          </View>
        </View>
      </Pressable>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Projets en cours</Text>
      {renderHeader()}
      <FlatList
        data={jobs}
        renderItem={renderJob}
        keyExtractor={(item, index) =>
          item?.job_id?.toString?.() || `job-${index}`
        }
      />

      <ProgressModal
        visible={!!selectedJob}
        job={selectedJob}
        onClose={() => setSelectedJob(null)}
        onReport={() => {}}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    borderRadius: 8,
    padding: 16,
    backgroundColor: 'white',
    margin: 16,
    borderColor: '#ccc',
    borderWidth: 1,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#0084FA',
    marginBottom: 12,
    textAlign: 'center',
  },
  headerRow: {
    borderBottomWidth: 1,
    borderBottomColor: '#ddd',
    paddingBottom: 4,
    marginBottom: 4,
  },
  columnTitle: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#333',
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomColor: '#eee',
    borderBottomWidth: 1,
  },
  jobTitle: {
    fontSize: 14,
    color: '#111',
  },
  progressContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  barAndLabel: {
    flex: 1,
  },
  progress: {
    height: 10,
    borderRadius: 4,
    backgroundColor: '#0084FA',
  },
  lateLabel: {
    fontSize: 12,
    color: '#0084FA',
    position: 'absolute',
    top: -16,
    right: 0,
  },
});

export default JobsInProgress;
