import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  Pressable,
  Linking,
  Alert,
  Modal,
  TextInput,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';
import { useAuth } from '@/hooks/useAuth';
import Toast from 'react-native-toast-message';
import { Icon } from 'react-native-paper';
import type { StudentInfo, StudentLinks } from '@/types/profileTypes';

export default function ProfileScreen() {
  const [student, setStudent] = useState<StudentInfo | null>(null);
  const [jobsDone, setJobsDone] = useState(0);
  const [jobsInProgress, setJobsInProgress] = useState(0);
  const [editing, setEditing] = useState(false);
  const [links, setLinks] = useState<StudentLinks | null>(null);

  const { logout } = useAuth();

  const loadProfile = async () => {
    try {
      const studentReq = await ApiActions.get({
        route: 'student',
        params: {
          firstname: '',
          lastname: '',
          email: '',
          section_name: '',
          promotion_name: '',
          current_unit_name: '',
          github: '',
          linkedin: '',
          cv: '',
          plesk: '',
          personal_website: '',
        },
      });
      const doneJobs = await ApiActions.get({
        route: 'job/done',
        params: {
          job_id: '',
        },
      });
      const inProgressJobs = await ApiActions.get({
        route: 'job/progress',
        params: {
          job_id: '',
        },
      });

      const data = studentReq?.data?.[0];
      setStudent(data);
      setLinks({
        github: data.student_github || '',
        plesk: data.student_plesk || '',
        linkedin: data.student_linkedin || '',
        cv: data.student_cv || '',
        personal_website: data.student_personal_website || '',
      });
      setJobsDone(doneJobs?.data?.length || 0);
      setJobsInProgress(inProgressJobs?.data?.length || 0);
    } catch (err) {
      Alert.alert('Erreur', 'Impossible de charger le profil');
    }
  };

  useEffect(() => {
    loadProfile();
  }, []);

  const openLink = (url: string) => {
    if (url) Linking.openURL(url);
  };

  const handleSaveLinks = async () => {
    try {
      if (links) {
        await ApiActions.put({ route: 'student', params: links });
      } else {
        throw new Error('Links cannot be null');
      }
      Toast.show({
        type: 'success',
        text1: 'Succès',
        text2: 'Liens mis à jour',
      });
      setEditing(false);
      loadProfile();
    } catch {
      Alert.alert('Erreur', 'Impossible de mettre à jour les liens');
    }
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <Text style={styles.title}>Mon profil</Text>

      {student && (
        <View style={styles.card}>
          <Text style={styles.name}>
            {student.student_firstname} {student.student_lastname}
          </Text>
          <Text style={styles.label}>{student.section_name}</Text>
          <Text style={styles.label}>{student.current_unit_name}</Text>

          <View style={styles.statsRow}>
            <View style={styles.statBox}>
              <Text style={styles.statNumber}>{jobsInProgress}</Text>
              <Text style={styles.statLabel}>En cours</Text>
            </View>
            <View style={styles.statBox}>
              <Text style={styles.statNumber}>{jobsDone}</Text>
              <Text style={styles.statLabel}>Terminés</Text>
            </View>
          </View>

          <View style={styles.linksContainer}>
            {[
              { label: 'GitHub', key: 'github', icon: 'github' },
              { label: 'Plesk', key: 'plesk', icon: 'web' },
              {
                label: 'Portfolio',
                key: 'personal_website',
                icon: 'briefcase',
              },
              { label: 'LinkedIn', key: 'linkedin', icon: 'linkedin' },
              { label: 'CV', key: 'cv', icon: 'file-document-outline' },
            ].map(({ label, key, icon }) => (
              <Pressable
                key={key}
                style={styles.linkRow}
                onPress={() => openLink((student as any)[`student_${key}`])}
              >
                <Icon source={icon} size={22} color="#0084FA" />
                <Text style={styles.linkLabel}>{label}</Text>
              </Pressable>
            ))}
          </View>

          <Pressable
            style={styles.primaryButton}
            onPress={() => setEditing(true)}
          >
            <Text style={styles.primaryText}>Modifier mes liens</Text>
          </Pressable>
          <Pressable style={styles.logoutButton} onPress={logout}>
            <Text style={styles.logoutText}>Se déconnecter</Text>
          </Pressable>
        </View>
      )}

      <Modal visible={editing} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <Text style={styles.modalTitle}>Modifier mes liens</Text>
            {links &&
              Object.entries(links).map(([key, value]) => (
                <TextInput
                  key={key}
                  placeholder={key.charAt(0).toUpperCase() + key.slice(1)}
                  value={value}
                  onChangeText={(text) =>
                    setLinks((prev) => ({
                      ...(prev || {
                        github: '',
                        plesk: '',
                        linkedin: '',
                        cv: '',
                        personal_website: '',
                      }),
                      [key]: text,
                    }))
                  }
                  style={styles.input}
                />
              ))}
            <Pressable style={styles.primaryButton} onPress={handleSaveLinks}>
              <Text style={styles.primaryText}>Sauvegarder</Text>
            </Pressable>
            <Pressable onPress={() => setEditing(false)}>
              <Text
                style={{ textAlign: 'center', color: '#444', marginTop: 12 }}
              >
                Annuler
              </Text>
            </Pressable>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 20,
    backgroundColor: '#f7f7f7',
    alignItems: 'center',
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#0084FA',
    marginBottom: 16,
  },
  card: {
    backgroundColor: '#fff',
    width: '100%',
    maxWidth: 500,
    padding: 20,
    borderRadius: 12,
    elevation: 1,
    alignItems: 'center',
  },
  name: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 4,
    color: '#222',
  },
  label: {
    color: '#666',
    fontSize: 14,
  },
  statsRow: {
    flexDirection: 'row',
    marginVertical: 20,
    justifyContent: 'space-around',
    width: '100%',
  },
  statBox: {
    alignItems: 'center',
  },
  statNumber: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#0084FA',
  },
  statLabel: {
    fontSize: 13,
    color: '#555',
  },
  linksContainer: {
    width: '100%',
    marginBottom: 20,
  },
  linkRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
  },
  linkLabel: {
    marginLeft: 10,
    color: '#0084FA',
    fontSize: 15,
  },
  primaryButton: {
    backgroundColor: '#0084FA',
    padding: 10,
    borderRadius: 8,
    width: '100%',
    alignItems: 'center',
    marginTop: 10,
  },
  primaryText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  logoutButton: {
    marginTop: 30,
    padding: 12,
    borderRadius: 8,
    backgroundColor: '#e91e63',
    width: '100%',
    maxWidth: 500,
    alignItems: 'center',
  },
  logoutText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  modalOverlay: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: 'rgba(0,0,0,0.4)',
    padding: 20,
  },
  modalContainer: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#0084FA',
    marginBottom: 12,
  },
  input: {
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 6,
    padding: 10,
    marginBottom: 12,
  },
});
