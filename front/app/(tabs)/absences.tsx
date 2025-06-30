import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  Button,
  StyleSheet,
  Alert,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
  FlatList,
  ScrollView,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { format } from 'date-fns';
import { ApiActions } from '../../services/ApiServices';

interface AbsenceForm {
  start_date: string;
  end_date: string;
  duration: number;
  reason: string;
  image: string | null;
  imageName: string;
}

interface UploadedAbsence {
  absence_start_date: string;
  absence_end_date: string;
  absence_duration: number;
  absence_status: number;
  absence_comment: string | null;
}

const UploadAbsences: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const [uploadedAbsences, setUploadedAbsences] = useState<UploadedAbsence[]>([]);
  const [absenceForm, setAbsenceForm] = useState<AbsenceForm>({
    start_date: '',
    end_date: '',
    duration: 0,
    reason: '',
    image: null,
    imageName: '',
  });

  const reasons = [
    'Accident de transport',
    'Maladie',
    'Raison familiale',
    'Evènement entreprise',
    'Télétravail',
    'Autre',
  ];

  useEffect(() => {
    fetchUploadedAbsences();
  }, []);

  const fetchUploadedAbsences = async () => {
    try {
      const response = await ApiActions.get({ route: 'absence', params: {
        id: "",
        start_date: "",
        end_date: "",
        duration: "",
        email: "",
        comment: "",
        status: "",
        link: "",
      } });
      if (response.status === 200) {
        setUploadedAbsences(response.data || []);
      }
    } catch (error) {
      console.error('Erreur récupération absences');
    }
  };

  const handleImagePick = async () => {
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 1,
    });

    if (!result.canceled) {
      setAbsenceForm((prev) => ({
        ...prev,
        image: result.assets[0].uri,
        imageName: result.assets[0].uri.split('/').pop() || '',
      }));
    }
  };

  const recapAbsence = () => `
    Raison : ${absenceForm.reason}
    Du ${absenceForm.start_date} au ${absenceForm.end_date}
    Durée : ${absenceForm.duration} ${absenceForm.duration > 1 ? 'jours ouvrés' : 'jour ouvré'}`;

  const handleUploadAbsence = () => {
    
    const { start_date, end_date, reason, image } = absenceForm;
    if (!start_date || !end_date || !reason || !image) {      
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    const start = new Date(start_date);
    const end = new Date(end_date);
    if (start >= end) {
      Alert.alert('Erreur', 'La date de début doit être antérieure à la date de retour');
      return;
    }

    let duration = 0;
    let temp = new Date(start);
    while (temp < end) {      
      if (temp.getDay() !== 0 && temp.getDay() !== 6) duration++;
      temp.setDate(temp.getDate() + 1);
    }

    Alert.alert('Confirmation', recapAbsence(), [
      { text: 'Annuler', style: 'cancel' },
      {
        text: 'Confirmer',
        onPress: async () => {          
          setLoading(true);
          try {
            const response = await ApiActions.post({
              route: 'uploadAbsence',
              params: { ...absenceForm, duration },
            });
            if (response.status === 200) {
              Alert.alert('Succès', 'Absence envoyée avec succès');
              setAbsenceForm({
                start_date: '',
                end_date: '',
                reason: '',
                image: null,
                imageName: '',
                duration: 0,
              });
              fetchUploadedAbsences();
            } else throw new Error();
          } catch (error) {
            Alert.alert('Erreur', "L'envoi de l'absence a échoué.");
          } finally {
            setLoading(false);
          }
        },
      },
    ]);
  };

  const renderAbsenceItem = ({ item }: { item: UploadedAbsence }) => (
    <View style={styles.absenceCard}>
      <Text style={styles.absenceText}>
        📅 Du {format(new Date(item.absence_start_date), 'dd/MM/yyyy')} au {format(new Date(item.absence_end_date), 'dd/MM/yyyy')}
      </Text>
      <Text style={styles.absenceText}>🕒 {item.absence_duration} jour(s)</Text>
      <Text style={styles.absenceText}>
        ✅ Statut : {item.absence_status === 1 ? 'Validée' : item.absence_status === 2 ? 'Refusée' : 'En attente'}
      </Text>
      {item.absence_comment && <Text style={styles.absenceText}>💬 {item.absence_comment}</Text>}
    </View>
  );

  return (
    <ScrollView contentContainerStyle={styles.container}>
      {!loading ? (
        <View style={styles.form}>
          <Text style={styles.title}>Nouvelle absence</Text>

          <Text style={styles.label}>Date de début</Text>
          {Platform.OS === 'web' ? (
            <TextInput
              style={styles.input}
              placeholder="YYYY-MM-DD"
              value={absenceForm.start_date}
              onChangeText={(text) => setAbsenceForm((p) => ({ ...p, start_date: text }))}
            />
          ) : (
            <TextInput
              style={styles.input}
              placeholder="YYYY-MM-DD"
              value={absenceForm.start_date}
              onChangeText={(text) => setAbsenceForm((p) => ({ ...p, start_date: text }))}
            />
          )}

          <Text style={styles.label}>Date de retour</Text>
          {Platform.OS === 'web' ? (
            <TextInput
              style={styles.input}
              placeholder="YYYY-MM-DD"
              value={absenceForm.end_date}
              onChangeText={(text) => setAbsenceForm((p) => ({ ...p, end_date: text }))}
            />
          ) : (
            <TextInput
              style={styles.input}
              placeholder="YYYY-MM-DD"
              value={absenceForm.end_date}
              onChangeText={(text) => setAbsenceForm((p) => ({ ...p, end_date: text }))}
            />
          )}

          <Text style={styles.label}>Motif</Text>
          {reasons.map((reason, index) => (
            <TouchableOpacity
              key={index}
              onPress={() => setAbsenceForm((p) => ({ ...p, reason }))}
              style={[styles.reasonButton, absenceForm.reason === reason && styles.selectedReason]}
            >
              <Text>{reason}</Text>
            </TouchableOpacity>
          ))}

          <TouchableOpacity onPress={handleImagePick} style={styles.uploadBtn}>
            <Text>📎 Joindre un justificatif</Text>
          </TouchableOpacity>

          {absenceForm.imageName && <Text style={styles.imageName}>🗂️ {absenceForm.imageName}</Text>}

          <Button title="Envoyer" onPress={handleUploadAbsence} />
        </View>
      ) : (
        <ActivityIndicator size="large" color="#1e88e5" />
      )}

      <Text style={styles.sectionTitle}>Absences précédentes</Text>
      <FlatList
        data={uploadedAbsences}
        keyExtractor={(item, index) => `${item.absence_start_date}-${index}`}
        renderItem={renderAbsenceItem}
        contentContainerStyle={styles.absenceList}
      />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 16,
    backgroundColor: '#f9f9f9',
  },
  form: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 10,
    marginBottom: 24,
    elevation: 2,
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 12,
  },
  input: {
    padding: 12,
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    marginBottom: 12,
    backgroundColor: '#fff',
  },
  label: {
    fontWeight: 'bold',
    marginBottom: 8,
  },
  reasonButton: {
    padding: 10,
    borderWidth: 1,
    borderColor: '#aaa',
    borderRadius: 6,
    marginBottom: 8,
  },
  selectedReason: {
    backgroundColor: '#cde2ff',
    borderColor: '#1e88e5',
  },
  uploadBtn: {
    padding: 12,
    backgroundColor: '#eee',
    borderRadius: 6,
    alignItems: 'center',
    marginVertical: 10,
  },
  imageName: {
    fontSize: 13,
    marginBottom: 10,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  absenceList: {
    paddingBottom: 50,
  },
  absenceCard: {
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 8,
    marginBottom: 10,
    elevation: 1,
  },
  absenceText: {
    fontSize: 13,
    marginBottom: 4,
  },
});

export default UploadAbsences;
