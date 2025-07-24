import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  Modal,
  StyleSheet,
  ActivityIndicator,
  ScrollView,
  Pressable,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';
import Toast from 'react-native-toast-message';

interface WaitingListModalProps {
  visible: boolean;
  groupId: string | number;
  onClose: () => void;
  onStudentAccepted?: () => void;
}

const WaitingListModal: React.FC<WaitingListModalProps> = ({
  visible,
  groupId,
  onClose,
}) => {
  const [waitingList, setWaitingList] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);

  const fetchWaitingList = async () => {
    setLoading(true);
    const res = await ApiActions.get({
      route: '/waitinglist',
      params: {
        group_id: groupId,
        student_id: '',
        student_email: '',
      },
    });
    if (res?.status === 200) {
      setWaitingList(res.data || []);
    } else {
      Toast.show({
        type: 'error',
        text1: 'Erreur',
        text2: "Impossible de récupérer la liste d'attente.",
      });
    }
    setLoading(false);
  };

  const acceptStudent = async (studentId: string | number) => {
    const res = await ApiActions.put({
      route: '/waitinglist',
      params: {
        group_id: groupId,
        student_id: studentId,
      },
    });
    if (res?.status === 200) {
      fetchWaitingList();
      Toast.show({
        type: 'success',
        text1: 'Étudiant accepté',
        text2: 'L’étudiant a été ajouté au groupe.',
      });
      //   onStudentAccepted?.();
    } else {
      Toast.show({
        type: 'error',
        text1: 'Erreur',
        text2: "Impossible d'accepter l'étudiant.",
      });
    }
  };

  useEffect(() => {
    if (visible && groupId) {
      fetchWaitingList();
    }
  }, [visible, groupId]);

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.modal}>
          <Text style={styles.title}>Demandes en attente</Text>
          {loading ? (
            <ActivityIndicator size="large" color="#3B82F6" />
          ) : (
            <ScrollView>
              {waitingList.length === 0 ? (
                <Text>Aucune demande pour rejoindre votre projet.</Text>
              ) : (
                waitingList.map((student) => (
                  <View key={student.student_id} style={styles.row}>
                    <Text>{student.student_email}</Text>
                    <Pressable
                      style={styles.acceptButton}
                      onPress={() => acceptStudent(student.student_id)}
                    >
                      <Text style={styles.acceptText}>Accepter</Text>
                    </Pressable>
                  </View>
                ))
              )}
              <Pressable style={styles.closeBtn} onPress={onClose}>
                <Text style={styles.closeText}>Fermer</Text>
              </Pressable>
            </ScrollView>
          )}
        </View>
      </View>
    </Modal>
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
    maxHeight: '85%',
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 12,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderColor: '#eee',
  },
  acceptButton: {
    backgroundColor: '#00c853',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
  },
  acceptText: {
    color: 'white',
    fontWeight: 'bold',
  },
  closeBtn: {
    marginTop: 20,
    backgroundColor: '#888',
    padding: 10,
    borderRadius: 6,
    alignItems: 'center',
  },
  closeText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});

export default WaitingListModal;
