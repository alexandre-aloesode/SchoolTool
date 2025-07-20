import React, { useState } from 'react';
import {
  View,
  Text,
  Modal,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Button,
  Platform,
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import * as ImagePicker from 'expo-image-picker';
import { format } from 'date-fns';
import { ApiActions } from '@/services/ApiServices';
import type { AbsenceForm } from '@/types/absencesTypes';

type Props = {
  visible: boolean;
  onClose: () => void;
  onSuccess: () => void;
};

const reasons = [
  'Accident de transport',
  'Maladie',
  'Raison familiale',
  'Evènement entreprise',
  'Télétravail',
  'Autre',
];

const AbsenceFormModal: React.FC<Props> = ({ visible, onClose, onSuccess }) => {
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState<AbsenceForm>({
    start_date: '',
    end_date: '',
    duration: 0,
    reason: '',
    image: null,
    imageName: '',
  });

  const [showStartPicker, setShowStartPicker] = useState(false);
  const [showEndPicker, setShowEndPicker] = useState(false);

  const pickImage = async () => {
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 1,
    });

    if (!result.canceled) {
      setForm((prev) => ({
        ...prev,
        image: result.assets[0].uri,
        imageName: result.assets[0].uri.split('/').pop() || '',
      }));
    }
  };

  const onDateChange = (
    _event: any,
    selectedDate: Date | undefined,
    type: 'start' | 'end',
  ) => {
    if (!selectedDate) return;

    const iso = selectedDate.toISOString().split('T')[0];
    setForm((prev) => ({
      ...prev,
      [type === 'start' ? 'start_date' : 'end_date']: iso,
    }));

    if (Platform.OS !== 'ios') {
      type === 'start' ? setShowStartPicker(false) : setShowEndPicker(false);
    }
  };

  const handleSubmit = () => {
    const { start_date, end_date, reason, image } = form;
    if (!start_date || !end_date || !reason || !image) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs.');
      return;
    }

    const start = new Date(start_date);
    const end = new Date(end_date);
    if (start >= end) {
      Alert.alert(
        'Erreur',
        'La date de début doit être antérieure à celle de retour.',
      );
      return;
    }

    let duration = 0;
    let temp = new Date(start);
    while (temp < end) {
      if (temp.getDay() !== 0 && temp.getDay() !== 6) duration++;
      temp.setDate(temp.getDate() + 1);
    }

    Alert.alert(
      'Confirmation',
      `Raison : ${reason}\nDu ${start_date} au ${end_date}\nDurée : ${duration} jour(s)`,
      [
        { text: 'Annuler', style: 'cancel' },
        {
          text: 'Envoyer',
          onPress: async () => {
            setLoading(true);
            try {
              const response = await ApiActions.post({
                route: 'uploadAbsence',
                params: { ...form, duration },
              });
              if (response?.status === 200) {
                Alert.alert('Succès', 'Absence envoyée.');
                setForm({
                  start_date: '',
                  end_date: '',
                  reason: '',
                  image: null,
                  imageName: '',
                  duration: 0,
                });
                onSuccess();
                onClose();
              } else throw new Error();
            } catch {
              Alert.alert('Erreur', "L'envoi a échoué.");
            } finally {
              setLoading(false);
            }
          },
        },
      ],
    );
  };

  const renderDateInput = (
    label: string,
    field: 'start_date' | 'end_date',
    showPicker: boolean,
    setShowPicker: (show: boolean) => void,
  ) => {
    const value = form[field];

    return (
      <>
        <Text style={styles.label}>{label}</Text>
        {Platform.OS === 'web' ? (
          <View style={styles.webDateWrapper}>
            <input
              type="date"
              value={value}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, [field]: e.target.value }))
              }
              style={styles.webDateInput}
            />
          </View>
        ) : (
          <>
            <TouchableOpacity
              onPress={() => setShowPicker(true)}
              style={styles.dateBtn}
            >
              <Text>
                {value ? format(new Date(value), 'dd/MM/yyyy') : 'Sélectionner'}
              </Text>
            </TouchableOpacity>
            {showPicker && (
              <DateTimePicker
                value={value ? new Date(value) : new Date()}
                mode="date"
                display="default"
                onChange={(e, d) =>
                  onDateChange(e, d, field === 'start_date' ? 'start' : 'end')
                }
              />
            )}
          </>
        )}
      </>
    );
  };

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.overlay}>
        <View style={styles.modal}>
          {loading ? (
            <ActivityIndicator size="large" color="#2196f3" />
          ) : (
            <>
              <Text style={styles.title}>Nouvelle absence</Text>

              {renderDateInput(
                'Date de début',
                'start_date',
                showStartPicker,
                setShowStartPicker,
              )}
              {renderDateInput(
                'Date de retour',
                'end_date',
                showEndPicker,
                setShowEndPicker,
              )}

              <Text style={styles.label}>Motif</Text>
              {reasons.map((r, i) => (
                <TouchableOpacity
                  key={i}
                  style={[
                    styles.reasonButton,
                    form.reason === r && styles.selected,
                  ]}
                  onPress={() => setForm((p) => ({ ...p, reason: r }))}
                >
                  <Text>{r}</Text>
                </TouchableOpacity>
              ))}

              <TouchableOpacity style={styles.uploadBtn} onPress={pickImage}>
                <Text>📎 Joindre un justificatif</Text>
              </TouchableOpacity>
              {form.imageName && (
                <Text style={styles.imageName}>🗂️ {form.imageName}</Text>
              )}

              <View style={styles.btnGroup}>
                <Button title="Annuler" onPress={onClose} />
                <Button title="Envoyer" onPress={handleSubmit} />
              </View>
            </>
          )}
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: 'rgba(0,0,0,0.5)',
    padding: 20,
  },
  modal: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    elevation: 3,
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 16,
    textAlign: 'center',
  },
  label: {
    fontWeight: 'bold',
    marginTop: 10,
  },
  dateBtn: {
    padding: 12,
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 6,
    marginTop: 6,
    marginBottom: 8,
    backgroundColor: '#fafafa',
  },
  webDateWrapper: {
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 6,
    overflow: 'hidden',
    marginTop: 6,
    marginBottom: 8,
  },
  webDateInput: {
    padding: '8px',
    fontSize: '14px',
    width: '100%',
    border: 'none',
    outline: 'none',
    boxSizing: 'border-box',
  } as any,
  reasonButton: {
    padding: 10,
    borderWidth: 1,
    borderColor: '#aaa',
    borderRadius: 6,
    marginTop: 8,
  },
  selected: {
    backgroundColor: '#cde2ff',
    borderColor: '#1e88e5',
  },
  uploadBtn: {
    marginTop: 14,
    backgroundColor: '#eee',
    borderRadius: 6,
    padding: 12,
    alignItems: 'center',
  },
  imageName: {
    fontSize: 13,
    marginTop: 6,
    color: '#555',
  },
  btnGroup: {
    marginTop: 20,
    gap: 10,
  },
});

export default AbsenceFormModal;
