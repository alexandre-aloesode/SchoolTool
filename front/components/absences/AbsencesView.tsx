import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  FlatList,
  Pressable,
} from 'react-native';
import { format } from 'date-fns';
import { ApiActions } from '@/services/ApiServices';
import AbsenceFormModal from './AbsenceFormModal';
import type { UploadedAbsence } from '@/types/absencesTypes';

const UploadAbsences: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const [uploadedAbsences, setUploadedAbsences] = useState<UploadedAbsence[]>([]);
  const [formVisible, setFormVisible] = useState(false);

  useEffect(() => {
    fetchUploadedAbsences();
  }, []);

  const fetchUploadedAbsences = async () => {
    setLoading(true);
    try {
      const response = await ApiActions.get({
        route: 'absence',
        params: {
          id: '',
          start_date: '',
          end_date: '',
          duration: '',
          email: '',
          comment: '',
          status: '',
          link: '',
        },
      });

      if (response?.status === 200) {
        setUploadedAbsences(response.data || []);
      }
    } catch (error) {
      console.error('Erreur récupération absences', error);
    } finally {
      setLoading(false);
    }
  };

  const renderAbsenceItem = ({ item }: { item: UploadedAbsence }) => (
    <View style={styles.row}>
      <Text style={[styles.cell, styles.cellText]}>
        {format(new Date(item.absence_start_date), 'dd/MM/yyyy')} -{' '}
        {format(new Date(item.absence_end_date), 'dd/MM/yyyy')}
      </Text>
      <Text style={[styles.cell, styles.cellText]}>{item.absence_duration} jour{item.absence_duration > 1 ? 's' : ''}</Text>
      <Text style={[styles.cell, styles.cellText]}>
        {item.absence_status === 1
          ? 'Validée'
          : item.absence_status === 2
            ? 'Refusée'
            : 'En attente'}
      </Text>
    </View>
  );

  return (
    <View style={styles.wrapper}>

      <Pressable style={styles.addButton} onPress={() => setFormVisible(true)}>
        <Text style={styles.addButtonText}>+ Nouvelle absence</Text>
      </Pressable>
      
      <Text style={styles.title}>Absences précédentes</Text>

      {loading ? (
        <ActivityIndicator size="large" color="#0084FA" style={{ marginTop: 20 }} />
      ) : (
        <View style={styles.tableContainer}>
          <View style={styles.headerRow}>
            <Text style={[styles.headerText, styles.cell]}>Période</Text>
            <Text style={[styles.headerText, styles.cell]}>Durée</Text>
            <Text style={[styles.headerText, styles.cell]}>Statut</Text>
          </View>

          <FlatList
            data={uploadedAbsences}
            keyExtractor={(item, index) => `${item.absence_start_date}-${index}`}
            renderItem={renderAbsenceItem}
            contentContainerStyle={{ paddingBottom: 20 }}
          />
        </View>
      )}

      <AbsenceFormModal
        visible={formVisible}
        onClose={() => setFormVisible(false)}
        onSuccess={fetchUploadedAbsences}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: {
    flex: 1,
    padding: 16,
    backgroundColor: '#fff',
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#0084FA',
    textAlign: 'center',
    marginBottom: 12,
  },
  addButton: {
    alignSelf: 'center',
    marginBottom: 16,
    paddingHorizontal: 16,
    paddingVertical: 8,
    backgroundColor: '#0084FA',
    borderRadius: 6,
  },
  addButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 15,
  },
  tableContainer: {
    borderTopWidth: 1,
    borderColor: '#ddd',
  },
  headerRow: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderColor: '#ddd',
    paddingVertical: 8,
    backgroundColor: '#f2f2f2',
  },
  headerText: {
    fontWeight: 'bold',
    fontSize: 14,
    color: '#333',
    textAlign: 'left',
  },
  row: {
    flexDirection: 'row',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderColor: '#f0f0f0',
  },
  cell: {
    flex: 1,
  },
  cellText: {
    fontSize: 14,
    color: '#333',
  },
});

export default UploadAbsences;
