import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  Modal,
  StyleSheet,
  ScrollView,
  Pressable,
  ActivityIndicator,
  Linking,
  Alert,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';
import ReviewModal from './ReviewModal';
import WaitingListModal from './WaitingListModal';
import type {
  ProgressModalProps,
  JobInProgress,
  JobSkills,
  JobGroupMembers,
} from '@/types/jobsTypes';
import Toast from 'react-native-toast-message';

const ProgressModal: React.FC<ProgressModalProps> = ({
  visible,
  job,
  onClose,
  onStudentAccepted,
}) => {
  const [loading, setLoading] = useState(false);
  const [skills, setSkills] = useState<JobSkills | null>(null);
  const [group, setGroup] = useState<JobGroupMembers | null>(null);
  const [jobData, setJobData] = useState<JobInProgress | null>(null);
  const [showReview, setShowReview] = useState(false);
  const [showWaitingList, setShowWaitingList] = useState(false);

  useEffect(() => {
    if (visible && job) {
      const fetchDetails = async () => {
        setLoading(true);
        try {
          const regReq = await ApiActions.get({
            route: '/registration',
            params: {
              job_name: '',
              group_name: '',
              group_id: '',
              group_is_valid: '',
              registration_id: job.registration_id,
              lead_email: '',
              job_description: '',
              min_students: '',
              max_students: '',
              member_is_lead: '',
              start_date: '',
              end_date: '',
              click_date: '',
              link_subject: '',
              job_duration: '',
              job_id: '',
              job_unit_name: '',
              job_is_complete: '',
              job_is_done: '',
              correction_date: '',
              order: 'job_unit_name',
            },
          });

          const skillsReq = await ApiActions.get({
            route: '/job/skill',
            params: {
              job_id: job.job_id,
              skill_name: '',
              skill_id: '',
              needed: '',
              earned: '',
            },
          });

          const membersReq = await ApiActions.get({
            route: '/group',
            params: {
              group_id: job.group_id,
              member: '',
            },
          });

          setJobData(regReq?.data?.[0] || job);
          setSkills(skillsReq?.data || []);
          setGroup(membersReq?.data[0] || []);
        } catch (err) {
          Toast.show({
            type: 'error',
            text1: 'Erreur',
            text2: 'Impossible de récupérer les détails du projet.',
          });
        } finally {
          setLoading(false);
        }
      };

      fetchDetails();
    }
  }, [visible, job]);

  const handleOpenInstructions = () => {
    if (jobData?.job_link_subject) {
      Linking.openURL(jobData.job_link_subject);
    } else {
      Alert.alert('Aucun lien de consigne disponible.');
    }
  };

  const handleReport = () => {
    setShowReview(true);
  };

  const handleMarkAsDone = async () => {
    const confirmed = confirm(
      'Êtes-vous sûr de vouloir marquer ce projet comme terminé ?',
    );
    if (!confirmed || !jobData?.group_id) return;

    try {
      await ApiActions.put({
        route: '/group/click',
        params: {
          group_id: jobData.group_id,
        },
      });
      alert('Le projet a été marqué comme terminé.');
      onClose();
    } catch (err) {
      Toast.show({
        type: 'error',
        text1: 'Erreur',
        text2: 'Impossible de marquer le projet comme terminé.',
      });
    }
  };

  return (
    <>
      <Modal
        visible={visible}
        animationType="slide"
        transparent
        onRequestClose={onClose}
      >
        <View style={styles.overlay}>
          <View style={styles.modal}>
            {loading ? (
              <ActivityIndicator size="large" color="#1188aa" />
            ) : (
              <ScrollView>
                <Text style={styles.title}>
                  [{jobData?.job_unit_name}] {jobData?.job_name}
                </Text>

                <Text style={styles.label}>Description :</Text>
                <Text>{jobData?.job_description}</Text>

                <Text style={styles.label}>Durée :</Text>
                <Text>{jobData?.job_duration} jours</Text>
                <Text>Début : {jobData?.start_date}</Text>
                <Text>Fin : {jobData?.end_date}</Text>

                <Text style={styles.label}>Compétences :</Text>
                {Array.isArray(skills) &&
                  skills.map((skill, i) => (
                    <Text key={i}>
                      • {skill.skill_name} ({skill.skill_needed} →{' '}
                      {skill.skill_earned})
                    </Text>
                  ))}

                <Text style={styles.label}>Membres :</Text>
                {Array.isArray(group?.member) &&
                  group?.member.map((m, i) => (
                    <Text key={i}>
                      • {m.student_firstname} {m.student_lastname}
                    </Text>
                  ))}

                <View style={styles.buttonRow}>
                  <Pressable
                    style={[styles.button, styles.instructionBtn]}
                    onPress={handleOpenInstructions}
                  >
                    <Text style={styles.buttonText}>Consignes</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.button, styles.reportBtn]}
                    onPress={handleReport}
                  >
                    <Text style={styles.buttonText}>Rapport</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.button, styles.doneBtn]}
                    onPress={handleMarkAsDone}
                  >
                    <Text style={styles.buttonText}>Rendre le projet</Text>
                  </Pressable>
                </View>

                {jobData &&
                  group &&
                  jobData.job_max_students &&
                  group?.member?.length < Number(jobData.job_max_students) &&
                  !jobData.click_date && (
                    <Pressable
                      style={[styles.button, styles.waitingListBtn]}
                      onPress={() => setShowWaitingList(true)}
                    >
                      <Text style={styles.buttonText}>Demandes en attente</Text>
                    </Pressable>
                  )}

                <Pressable style={styles.closeBtn} onPress={onClose}>
                  <Text style={styles.closeText}>Fermer</Text>
                </Pressable>
              </ScrollView>
            )}
          </View>
        </View>
      </Modal>

      <ReviewModal
        visible={showReview}
        groupId={jobData?.group_id ?? null}
        onClose={() => setShowReview(false)}
      />
      <WaitingListModal
        visible={showWaitingList}
        groupId={jobData?.group_id ?? ''}
        onClose={() => setShowWaitingList(false)}
        onStudentAccepted={onStudentAccepted}
      />
    </>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modal: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    width: '90%',
    maxHeight: '90%',
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10,
    color: '#e91e63',
  },
  label: {
    marginTop: 10,
    fontWeight: '600',
    marginBottom: 4,
    color: '#0084FA',
  },
  closeBtn: {
    marginTop: 20,
    backgroundColor: '#ccc',
    padding: 10,
    borderRadius: 6,
    alignItems: 'center',
  },
  closeText: {
    fontWeight: 'bold',
  },
  buttonRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 20,
    flexWrap: 'wrap',
    gap: 8,
  },
  button: {
    flex: 1,
    padding: 10,
    marginHorizontal: 4,
    borderRadius: 6,
    alignItems: 'center',
  },
  instructionBtn: {
    backgroundColor: '#00acc1',
  },
  reportBtn: {
    backgroundColor: '#1976d2',
  },
  doneBtn: {
    backgroundColor: '#e91e63',
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  waitingListBtn: {
    backgroundColor: '#ffa000',
    marginTop: 10,
  },
});

export default ProgressModal;
