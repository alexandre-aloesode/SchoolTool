import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, StyleSheet } from 'react-native';
import { IconButton } from 'react-native-paper';
import { Picker } from '@react-native-picker/picker';
import { ApiActions } from '@/services/ApiServices';

const JobsAvailable = () => {
  const [jobsAvailable, setJobsAvailable] = useState([]);
  const [selectedUnit, setSelectedUnit] = useState("All");

  const getJobsAvailable = async () => {
    const jobsRequest = await ApiActions.get({
      route: 'student/job/available',
      params: {
        job_id: "",
        job_name: "",
        job_unit_id: "",
        job_unit_name: "",
      },
    });
    if (jobsRequest) {
      setJobsAvailable(jobsRequest.data);
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
          {[...new Set(jobsAvailable.map(job => job.job_unit_name))].map((unit, index) => (
            <Picker.Item label={unit} value={unit} key={index} />
          ))}
        </Picker>
      </View>
    </View>
  );

  const renderJob = ({ item }) => {
    if (selectedUnit !== "All" && item.job_unit_name !== selectedUnit) return null;

    return (
      <View style={styles.row}>
        <Text style={[styles.jobTitle, { flex: 1 }]}>{item.job_name}</Text>
        <View style={[styles.rightSide, { flex: 2 }]}>
          <Text style={styles.unitText}>{item.job_unit_name}</Text>
          <View style={styles.iconGroup}>
            <IconButton icon="check-circle-outline" size={18} onPress={() => {}} />
            <IconButton icon="magnify" size={18} onPress={() => {}} />
          </View>
        </View>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Projets disponibles</Text>
      {renderHeader()}
      <FlatList
        data={jobsAvailable}
        renderItem={renderJob}
        keyExtractor={(item, index) => item?.job_id?.toString?.() || `job-${index}`}
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
    color: '#e91e63',
    marginBottom: 12,
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
  iconGroup: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  rightSide: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  picker: {
    height: 30,
    width: '100%',
    marginTop: -8,
  },
});

export default JobsAvailable;
