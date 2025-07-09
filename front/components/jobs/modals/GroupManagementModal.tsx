import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  Modal,
  StyleSheet,
  TextInput,
  Pressable,
  ScrollView,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';
import { GroupManagementModalProps, JobGroups } from '@/types/jobs';

const GroupManagementModal: React.FC<GroupManagementModalProps> = ({ visible, jobId, onClose }) => {
  const [groups, setGroups] = useState<JobGroups | null>(null);
  const [groupName, setGroupName] = useState('');

  useEffect(() => {
    if (visible && jobId) {
      fetchGroups();
    }
  }, [visible, jobId]);

  const fetchGroups = async () => {
    const res = await ApiActions.get({
      route: '/group/available',
      params: {
        job_id: jobId,
        group_id: '',
        group_name: '',
        lead_firstname: '',
        lead_lastname: '',
      },
    });
    if (!res) {
      console.error("Erreur: aucune réponse de l'API");
      return;
    }
    
    if (res.status === 200) {
      setGroups(res.data || []);
    }
  };

  const createGroup = async () => {
    if (groupName.length < 5) return;
    await ApiActions.post({
      route: '/group',
      params: {
        job_id: jobId,
        group_name: groupName,
      },
    });
    setGroupName('');
    fetchGroups();
  };

  const askToJoinGroup = async (groupId : number | string) => {
    await ApiActions.post({
      route: '/waitinglist',
      params: {
        group_id: groupId,
      },
    });
    fetchGroups();
  };

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.modal}>
          <Text style={styles.title}>Groupes disponibles</Text>
          <ScrollView>
            {Array.isArray(groups) && groups.length > 0 ? (
              groups.map((g) => (
                <View key={g.group_id} style={styles.groupRow}>
                  <Text style={styles.groupName}>{g.group_name}</Text>
                  <Text>
                    {g.lead_firstname} {g.lead_lastname}
                  </Text>
                  <Pressable
                    onPress={() => askToJoinGroup(g.group_id)}
                    style={styles.joinBtn}
                  >
                    <Text style={styles.joinText}>Rejoindre</Text>
                  </Pressable>
                </View>
              ))
            ) : (
              <Text style={{ marginBottom: 10 }}>
                Aucun groupe disponible pour ce projet.
              </Text>
            )}

            <Text style={styles.subTitle}>Créer un groupe</Text>
            <View style={styles.createRow}>
              <TextInput
                value={groupName}
                onChangeText={setGroupName}
                placeholder="Nom du groupe"
                style={styles.input}
              />
              <Pressable style={styles.createBtn} onPress={createGroup}>
                <Text style={styles.createText}>Créer</Text>
              </Pressable>
            </View>
            <Pressable style={styles.closeBtn} onPress={onClose}>
              <Text style={styles.closeText}>Fermer</Text>
            </Pressable>
          </ScrollView>
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
    padding: 20,
    borderRadius: 10,
    width: '90%',
    maxHeight: '85%',
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#e91e63',
    marginBottom: 10,
  },
  subTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginTop: 20,
  },
  groupRow: {
    marginBottom: 10,
    paddingVertical: 6,
    borderBottomWidth: 1,
    borderColor: '#ddd',
  },
  groupName: {
    fontWeight: 'bold',
  },
  joinBtn: {
    marginTop: 6,
    backgroundColor: '#007bff',
    padding: 6,
    borderRadius: 4,
    alignSelf: 'flex-start',
  },
  joinText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  createRow: {
    flexDirection: 'row',
    marginTop: 10,
  },
  input: {
    flex: 1,
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 4,
    padding: 8,
    marginRight: 8,
  },
  createBtn: {
    backgroundColor: '#0044ff',
    padding: 10,
    borderRadius: 6,
  },
  createText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  closeBtn: {
    marginTop: 20,
    alignSelf: 'center',
    backgroundColor: '#888',
    padding: 8,
    borderRadius: 6,
  },
  closeText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});

export default GroupManagementModal;
