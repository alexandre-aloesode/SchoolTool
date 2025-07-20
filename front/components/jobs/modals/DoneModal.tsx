import React, { useState } from 'react';
import { Modal, View, Text, StyleSheet, ScrollView } from 'react-native';
import { Button } from 'react-native-paper';
import type { JobDone } from '@/types/jobsTypes';
import ReviewModal from './ReviewModal';

type DoneModalProps = {
  job: JobDone | null;
  visible: boolean;
  onClose: () => void;
};

const DoneModal: React.FC<DoneModalProps> = ({ job, visible, onClose }) => {
  const [showReview, setShowReview] = useState(false);

  if (!job) return null;

  return (
    <>
      <Modal animationType="slide" transparent={true} visible={visible}>
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <ScrollView>
              <Text style={styles.modalTitle}>
                [{job.job_unit_name}] {job.job_name}
              </Text>

              <Text style={styles.modalSubtitle}>{job.group_name}</Text>
              <Text style={styles.modalDescription}>
                {job.job_description || 'Aucune description disponible.'}
              </Text>

              <Text style={styles.modalInfo}>
                👨‍🏫 Chef de groupe : {job.lead_email}
              </Text>
              <Text style={styles.modalInfo}>📅 Début : {job.start_date}</Text>
              <Text style={styles.modalInfo}>📅 Fin : {job.end_date}</Text>

              <Button
                mode="contained"
                onPress={onClose}
                style={{ marginTop: 16 }}
              >
                Fermer
              </Button>
              <Button
                mode="outlined"
                onPress={() => {
                  setShowReview(true);
                }}
                style={{ marginTop: 12 }}
              >
                Rapport
              </Button>
            </ScrollView>
          </View>
        </View>
      </Modal>

      <ReviewModal
        visible={showReview}
        groupId={job?.group_id ?? null}
        onClose={() => setShowReview(false)}
      />
    </>
  );
};

const styles = StyleSheet.create({
  modalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    padding: 16,
  },
  modalContent: {
    backgroundColor: 'white',
    borderRadius: 12,
    padding: 20,
    elevation: 5,
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#e91e63',
    marginBottom: 8,
  },
  modalSubtitle: {
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 6,
  },
  modalDescription: {
    fontSize: 13,
    marginBottom: 8,
    color: '#333',
  },
  modalInfo: {
    fontSize: 13,
    marginBottom: 4,
    color: '#555',
  },
});

export default DoneModal;
