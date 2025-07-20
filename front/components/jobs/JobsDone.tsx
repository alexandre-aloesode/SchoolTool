import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  FlatList,
  StyleSheet,
  Dimensions,
  Pressable,
} from 'react-native';
import { Picker } from '@react-native-picker/picker';
import { ApiActions } from '@/services/ApiServices';
import DoneModal from './modals/DoneModal';
import type { JobDone, JobUnit, JobPromotion } from '@/types/jobsTypes';

const screenWidth = Dimensions.get('window').width;

const JobsDone = () => {
  const [promotions, setPromotions] = useState<JobPromotion[]>([]);
  const [units, setUnits] = useState<JobUnit[]>([]);
  const [jobsDone, setJobsDone] = useState<JobDone[]>([]);

  const [selectedPromotion, setSelectedPromotion] = useState('');
  const [selectedUnit, setSelectedUnit] = useState('all');

  const [selectedJob, setSelectedJob] = useState<JobDone | null>(null);

  useEffect(() => {
    fetchInitialData();
  }, []);

  useEffect(() => {
    if (selectedPromotion) {
      loadUnitsAndJobs();
    }
  }, [selectedPromotion]);

  const fetchInitialData = async () => {
    const history = await ApiActions.get({
      route: 'promotion/history',
      params: {
        promotion_id: '',
        promotion_name: '',
      },
    });

    if (history?.status === 200) {
      setPromotions(history.data || []);
      setSelectedPromotion(history.data[0]?.promotion_id || '');
    }
  };

  const loadUnitsAndJobs = async () => {
    const allUnits = await ApiActions.get({
      route: 'promotion/unit',
      params: {
        promotion_id: selectedPromotion,
        unit_id: '',
        unit_name: '',
      },
    });

    if (allUnits?.status === 200) {
      setUnits(allUnits.data || []);

      const allUnitIds = allUnits.data.map((u: JobUnit) => u.unit_id);
      const jobsResponse = await ApiActions.get({
        route: 'job/done',
        params: {
          job_name: '',
          registration_id: '',
          job_unit_name: '',
          job_unit_id: allUnitIds,
          job_description: '',
          start_date: '',
          end_date: '',
          group_name: '',
          lead_email: '',
          order: 'click_date',
          group_id: '',
          desc: '',
        },
      });

      if (jobsResponse?.status === 200) {
        setJobsDone(jobsResponse.data || []);
      }
    }
  };

  const filteredJobs = () => {
    if (selectedUnit === 'all') return jobsDone;
    return jobsDone.filter((job) => job.job_unit_id === selectedUnit);
  };

  const renderJob = ({ item }: { item: JobDone }) => (
    <Pressable style={styles.row} onPress={() => setSelectedJob(item)}>
      <Text style={[styles.jobTitle, { flex: 1 }]}>{item.job_name}</Text>
      <View style={[styles.jobDetails, { flex: 2 }]}>
        <Text style={styles.unitText}>{item.job_unit_name}</Text>
      </View>
    </Pressable>
  );

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Projets finis</Text>

      <View style={styles.selectorsContainer}>
        <View style={styles.pickerWrapper}>
          <Text style={styles.pickerLabel}>Promotion</Text>
          <Picker
            selectedValue={selectedPromotion}
            onValueChange={(val) => {
              setSelectedPromotion(val);
              setSelectedUnit('all');
            }}
            style={styles.picker}
          >
            {promotions.map((promo) => (
              <Picker.Item
                label={promo.promotion_name}
                value={promo.promotion_id}
                key={promo.promotion_id}
              />
            ))}
          </Picker>
        </View>

        <View style={styles.pickerWrapper}>
          <Text style={styles.pickerLabel}>Unité</Text>
          <Picker
            selectedValue={selectedUnit}
            onValueChange={(val) => setSelectedUnit(val)}
            style={styles.picker}
          >
            <Picker.Item label="Toutes les unités" value="all" key="all" />
            {units.map((unit) => (
              <Picker.Item
                label={unit.unit_name}
                value={unit.unit_id}
                key={unit.unit_id}
              />
            ))}
          </Picker>
        </View>
      </View>

      <FlatList
        data={filteredJobs()}
        renderItem={renderJob}
        keyExtractor={(item, index) =>
          item?.registration_id?.toString?.() || `job-${index}`
        }
      />

      <DoneModal
        job={selectedJob}
        visible={!!selectedJob}
        onClose={() => setSelectedJob(null)}
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
    flex: 1,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#e91e63',
    marginBottom: 12,
  },
  selectorsContainer: {
    flexDirection: screenWidth < 500 ? 'column' : 'row',
    gap: 12,
    marginBottom: 12,
  },
  pickerWrapper: {
    flex: 1,
  },
  pickerLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: '#555',
    marginBottom: 4,
  },
  picker: {
    height: 42,
    backgroundColor: '#f9f9f9',
    borderColor: '#ccc',
    borderWidth: 1,
    borderRadius: 4,
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
  jobDetails: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    alignItems: 'center',
  },
});

export default JobsDone;
