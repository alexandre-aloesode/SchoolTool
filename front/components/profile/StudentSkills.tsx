import React, { useEffect, useState, useContext } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Pressable,
  Linking,
  ScrollView,
  Alert,
} from 'react-native';
import { Icon } from 'react-native-paper';
import { useNavigation } from 'expo-router';
import { ApiActions } from '@/services/ApiServices';
import AuthContext from '@/context/authContext';

export default function StudentSkills() {
  const [student, setStudent] = useState(null);
  const [jobsDone, setJobsDone] = useState(0);
  const [jobsInProgress, setJobsInProgress] = useState(0);
  const auth = useContext(AuthContext);
  const navigation = useNavigation();

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

      setStudent(studentReq?.data?.[0]);
      setJobsDone(doneJobs?.data?.length || 0);
      setJobsInProgress(inProgressJobs?.data?.length || 0);
    } catch (err) {
      Alert.alert('Erreur', 'Impossible de charger le profil');
    }
  };

  useEffect(() => {
    loadProfile();
  }, []);

  const openLink = (url) => {
    if (url) Linking.openURL(url);
  };

  const handleLogout = () => {
    auth.logout();
    navigation.replace('/');
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <Text style={styles.title}>Compétences</Text>
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
            <Pressable
              style={styles.link}
              onPress={() => openLink(student.student_github)}
            >
              <Icon source="github" size={24} />
              <Text style={styles.linkLabel}>Github</Text>
            </Pressable>
            <Pressable
              style={styles.link}
              onPress={() => openLink(student.student_plesk)}
            >
              <Icon source="web" size={24} />
              <Text style={styles.linkLabel}>Plesk</Text>
            </Pressable>
            <Pressable
              style={styles.link}
              onPress={() => openLink(student.student_personal_website)}
            >
              <Icon source="briefcase" size={24} />
              <Text style={styles.linkLabel}>Portfolio</Text>
            </Pressable>
            <Pressable
              style={styles.link}
              onPress={() => openLink(student.student_linkedin)}
            >
              <Icon source="linkedin" size={24} />
              <Text style={styles.linkLabel}>LinkedIn</Text>
            </Pressable>
            <Pressable
              style={styles.link}
              onPress={() => openLink(student.student_cv)}
            >
              <Icon source="file" size={24} />
              <Text style={styles.linkLabel}>CV</Text>
            </Pressable>
          </View>
        </>
      )}

      <Pressable style={styles.logoutButton} onPress={handleLogout}>
        <Text style={styles.logoutText}>Se déconnecter</Text>
      </Pressable>
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
});
