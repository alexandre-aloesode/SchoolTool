import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, StyleSheet, Pressable } from 'react-native';
import { Picker } from '@react-native-picker/picker';
import { ApiActions } from '@/services/ApiServices';
import AvailableModal from './modals/AvailableModal';
import type { JobAvailable } from '@/types/jobsTypes';

const JobsAvailable = () => {
  const [jobsAvailable, setJobsAvailable] = useState<JobAvailable[]>([]);
  const [selectedUnit, setSelectedUnit] = useState('All');
  const [selectedJob, setSelectedJob] = useState<JobAvailable | null>(null);

  const getJobsAvailable = async () => {
    const jobsRequest = await ApiActions.get({
      route: 'student/job/available',
      params: {
        job_name: '',
        job_id: '',
        job_description: '',
        job_min_students: '',
        job_max_students: '',
        job_duration: '',
        job_unit_name: '',
        order: 'job_unit_name',
      },
    });

    if (!jobsRequest) {
      console.error("Erreur: aucune réponse de l'API");
      return;
    }

    if (jobsRequest.status === 200) {
      setJobsAvailable(jobsRequest.data || []);
    }
  };

  useEffect(() => {
    getJobsAvailable();
  }, []);

  const renderHeader = () => (
    <View style={[styles.row, styles.headerRow]}>
      <Text style={[styles.columnTitle, { flex: 1 }]}>Nom</Text>
      <View style={{ flex: 2 }}>
        <Picker
          selectedValue={selectedUnit}
          style={styles.picker}
          onValueChange={(itemValue) => setSelectedUnit(itemValue)}
        >
          <Picker.Item label="Toutes les Units" value="All" key="all" />
          {[...new Set(jobsAvailable.map((job) => job.job_unit_name))].map(
            (unit, index) => (
              <Picker.Item label={unit} value={unit} key={index} />
            ),
          )}
        </Picker>
      </View>
    </View>
  );

  const renderJob = ({ item }: { item: JobAvailable }) => {
    if (selectedUnit !== 'All' && item.job_unit_name !== selectedUnit)
      return null;

    return (
      <Pressable style={styles.row} onPress={() => setSelectedJob(item)}>
        <Text style={[styles.jobTitle, { flex: 1 }]}>{item.job_name}</Text>
        <View style={{ flex: 2 }}>
          <Text style={styles.unitText}>{item.job_unit_name}</Text>
        </View>
      </Pressable>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Projets disponibles</Text>
      {renderHeader()}
      <FlatList
        data={jobsAvailable}
        renderItem={renderJob}
        keyExtractor={(item, index) =>
          item?.job_id?.toString?.() || `job-${index}`
        }
      />
      <AvailableModal
        visible={!!selectedJob}
        job={selectedJob}
        onClose={() => setSelectedJob(null)}
        onGroupCreated={() => {
          setSelectedJob(null);
          getJobsAvailable();
        }}
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
    paddingBottom: 8,
    marginBottom: 8,
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
    fontWeight: 'bold',
    color: '#111',
  },
  unitText: {
    fontSize: 13,
    color: '#444',
  },
  picker: {
    height: 30,
    width: '100%',
    marginTop: -8,
  },
});

export default JobsAvailable;
