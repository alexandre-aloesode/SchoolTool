import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  FlatList,
  Button,
} from 'react-native';
import { format } from 'date-fns';
import { ApiActions } from '@/services/ApiServices';
import AbsenceFormModal from './AbsenceFormModal';
import type { UploadedAbsence } from '@/types/absencesTypes';

const UploadAbsences: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const [uploadedAbsences, setUploadedAbsences] = useState<UploadedAbsence[]>(
    [],
  );
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
    <View style={styles.absenceCard}>
      <Text style={styles.absenceText}>
        📅 Du {format(new Date(item.absence_start_date), 'dd/MM/yyyy')} au{' '}
        {format(new Date(item.absence_end_date), 'dd/MM/yyyy')}
      </Text>
      <Text style={styles.absenceText}>🕒 {item.absence_duration} jour(s)</Text>
      <Text style={styles.absenceText}>
        ✅ Statut :{' '}
        {item.absence_status === 1
          ? 'Validée'
          : item.absence_status === 2
            ? 'Refusée'
            : 'En attente'}
      </Text>
      {!!item.absence_comment && (
        <Text style={styles.absenceText}>💬 {item.absence_comment}</Text>
      )}
    </View>
  );

  return (
    <View style={styles.wrapper}>
      <View style={styles.buttonContainer}>
        <Button title="Nouvelle absence" onPress={() => setFormVisible(true)} />
      </View>

      <Text style={styles.sectionTitle}>Absences précédentes</Text>

      {loading ? (
        <ActivityIndicator
          size="large"
          color="#1e88e5"
          style={{ marginTop: 20 }}
        />
      ) : (
        <FlatList
          data={uploadedAbsences}
          keyExtractor={(item, index) => `${item.absence_start_date}-${index}`}
          renderItem={renderAbsenceItem}
          contentContainerStyle={styles.listContainer}
        />
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
    backgroundColor: '#f9f9f9',
  },
  buttonContainer: {
    alignItems: 'center',
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#0084FA',
    marginBottom: 12,
    textAlign: 'center',
  },
  listContainer: {
    paddingBottom: 40,
  },
  absenceCard: {
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 8,
    marginBottom: 12,
    elevation: 1,
    width: '100%',
    maxWidth: 500,
    alignSelf: 'center',
  },
  absenceText: {
    fontSize: 13,
    marginBottom: 4,
    color: '#333',
  },
});

export default UploadAbsences;
