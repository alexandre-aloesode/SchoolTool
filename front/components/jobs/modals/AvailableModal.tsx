import React, { useState } from 'react';
import {
  View,
  Text,
  Modal,
  StyleSheet,
  ScrollView,
  Pressable,
} from 'react-native';
import GroupManagementModal from './GroupManagementModal';

const AvailableModal = ({ visible, job, onClose }) => {
  const [groupVisible, setGroupVisible] = useState(false);

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent
      onRequestClose={onClose}
    >
      <View style={styles.modalOverlay}>
        <View style={styles.modalContent}>
          <ScrollView>
            <Text style={styles.modalTitle}>
              [{job?.job_unit_name}] {job?.job_name}
            </Text>
            <Text style={styles.label}>Description :</Text>
            <Text>{job?.job_description}</Text>

            <Text style={styles.label}>Durée :</Text>
            <Text>{job?.job_duration} jours</Text>

            <Text style={styles.label}>Participants :</Text>
            <Text>
              {job?.job_min_students} à {job?.job_max_students}
            </Text>

            <Pressable
              style={styles.groupBtn}
              onPress={() => setGroupVisible(true)}
            >
              <Text style={styles.groupText}>Groupe</Text>
            </Pressable>

            <Pressable style={styles.closeBtn} onPress={onClose}>
              <Text style={styles.closeText}>Fermer</Text>
            </Pressable>
          </ScrollView>
        </View>
        <GroupManagementModal
          visible={groupVisible}
          jobId={job?.job_id}
          onClose={() => setGroupVisible(false)}
        />
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#fff',
    padding: 20,
    borderRadius: 10,
    width: '85%',
    maxHeight: '80%',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  label: {
    marginTop: 10,
    fontWeight: '600',
  },
  closeBtn: {
    marginTop: 20,
    backgroundColor: '#1188aa',
    padding: 10,
    borderRadius: 6,
    alignItems: 'center',
  },
  closeText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  groupBtn: {
    marginTop: 15,
    backgroundColor: '#0044ff',
    padding: 10,
    borderRadius: 6,
    alignItems: 'center',
  },
  groupText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});

export default AvailableModal;
