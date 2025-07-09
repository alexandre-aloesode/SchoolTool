import React, { useEffect, useState, useContext } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Pressable,
  Linking,
  ScrollView,
  Alert,
  Modal,
  TextInput,
} from 'react-native';
import { Icon, Button } from 'react-native-paper';
import { ApiActions } from '@/services/ApiServices';
import { useAuth } from '@/hooks/useAuth';
import Toast from 'react-native-toast-message';
import { StudentInfo, StudentLinks } from '@/types/profileTypes';

export default function ProfileScreen() {
  const [student, setStudent] = useState<StudentInfo | null>(null);
  const [jobsDone, setJobsDone] = useState(0);
  const [jobsInProgress, setJobsInProgress] = useState(0);
  const [editing, setEditing] = useState(false);
  const [links, setLinks] = useState<StudentLinks | null>(null);
  // const [links, setLinks] = useState<StudentLinks | null>({
  //   github: '',
  //   plesk: '',
  //   linkedin: '',
  //   cv: '',
  //   personal_website: '',
  // });
  
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

  const openLink = (url : string) => {
    if (url) Linking.openURL(url);
  };

  const handleLogout = () => {
    logout();
  };

  const handleSaveLinks = async () => {
    // if (!links || !links.github || !links.plesk || !links.linkedin || !links.cv || !links.personal_website) {
    //   Toast.show({
    //     type: 'error',
    //     text1: 'Erreur',
    //     text2: 'Veuillez remplir tous les champs avant de sauvegarder.',
    //   });
    //   return;
    // }
    try {
      await ApiActions.put({
        route: 'student',
        params: {
          github: links?.github,
          plesk: links?.plesk,
          linkedin: links?.linkedin,
          cv: links?.cv,
          personal_website: links?.personal_website,
        },
      });
      setEditing(false);
      Toast.show({
        type: 'success',
        text1: 'Liens mis à jour',
        text2: 'Vos liens ont été mis à jour avec succès.',
      });
      loadProfile();
    } catch (err) {
      Alert.alert('Erreur', 'Échec de la mise à jour des liens');
    }
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <Text style={styles.title}>Profil</Text>
      {student && (
        <>
          <View style={styles.row}>
            <Icon source="account" size={24} color="#0084FA" />
            <Text style={styles.label}>
              {student.student_firstname} {student.student_lastname}
            </Text>
          </View>
          <View style={styles.row}>
            <Icon source="calendar" size={24} color="#0084FA" />
            <Text style={styles.label}>{student.current_unit_name}</Text>
          </View>
          <View style={styles.row}>
            <Icon source="school" size={24} color="#0084FA" />
            <Text style={styles.label}>{student.section_name}</Text>
          </View>
          <View style={styles.row}>
            <Icon source="progress-clock" size={24} color="#0084FA" />
            <Text style={styles.label}>
              {jobsInProgress} projet(s) en cours
            </Text>
          </View>
          <View style={styles.row}>
            <Icon source="check-circle-outline" size={24} color="#0084FA" />
            <Text style={styles.label}>{jobsDone} projet(s) fini(s)</Text>
          </View>

          <View style={styles.linksContainer}>
            {[
              { label: 'Github', key: 'github', icon: 'github' },
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
                style={styles.link}
                onPress={() => openLink((student as any)[`student_${key}`])}
              >
                <Icon source={icon} size={24} />
                <Text style={styles.linkLabel}>{label}</Text>
              </Pressable>
            ))}
          </View>

          <Pressable style={styles.editButton} onPress={() => setEditing(true)}>
            <Text style={styles.logoutText}>Modifier mes liens</Text>
          </Pressable>
        </>
      )}

      <Pressable style={styles.logoutButton} onPress={handleLogout}>
        <Text style={styles.logoutText}>Se déconnecter</Text>
      </Pressable>

      <Modal visible={editing} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <Text style={styles.modalTitle}>Modifier mes liens</Text>
            {links && Object.entries(links).map(([key, value]) => (
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
            <Button mode="contained" onPress={handleSaveLinks}>
              Sauvegarder
            </Button>
            <Button onPress={() => setEditing(false)}>Annuler</Button>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 20,
    backgroundColor: 'white',
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#e91e63',
    marginBottom: 16,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  label: {
    marginLeft: 10,
    fontSize: 16,
  },
  linksContainer: {
    marginTop: 24,
  },
  link: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  linkLabel: {
    marginLeft: 8,
    fontSize: 16,
    color: '#0084FA',
  },
  logoutButton: {
    marginTop: 32,
    backgroundColor: '#e91e63',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  logoutText: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 16,
  },
  editButton: {
    marginTop: 16,
    backgroundColor: '#0084FA',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalOverlay: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: 'rgba(0,0,0,0.5)',
    padding: 20,
  },
  modalContainer: {
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 20,
  },
  modalTitle: {
    fontSize: 18,
    marginBottom: 12,
    fontWeight: 'bold',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 6,
    padding: 8,
    marginBottom: 12,
  },
});
