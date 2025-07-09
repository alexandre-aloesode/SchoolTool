import React, { useEffect, useState } from 'react';
import {
  Modal,
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  ScrollView,
  Pressable,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';
import { JobReview, ReviewModalProps } from '@/types/jobsTypes';

const ReviewModal: React.FC<ReviewModalProps> = ({ visible, groupId, onClose }) => {
  const [loading, setLoading] = useState(false);
  const [review, setReview] = useState<JobReview | null>(null);

  useEffect(() => {
    if (visible && groupId) {
      const fetchReview = async () => {
        setLoading(true);
        try {
          const response = await ApiActions.get({
            route: '/group/review',
            params: {
              group_id: groupId,
            },
          });
          setReview(response?.data || null);
        } catch (err) {
          console.error('Erreur de chargement du rapport', err);
        } finally {
          setLoading(false);
        }
      };

      fetchReview();
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
          {loading ? (
            <ActivityIndicator size="large" color="#1188aa" />
          ) : (
            <ScrollView>
              <Text style={styles.title}>Rapport de correction</Text>

              {review ? (
                <>
                  <Text style={styles.label}>Correcteur :</Text>
                  <Text style={styles.value}>{review.corrector}</Text>

                  <Text style={styles.label}>Compétences :</Text>
                  <View style={styles.table}>
                    {review.skill.map((s, i) => (
                      <View key={i} style={styles.skillRow}>
                        <Text style={styles.skillName}>{s.skill_name}</Text>
                        <Text style={styles.skillPts}>
                          {s.job_skill_earned}
                        </Text>
                        <Text style={styles.skillStatus}>{s.skill_status}</Text>
                      </View>
                    ))}
                  </View>

                  <Text style={styles.label}>Commentaire :</Text>
                  <Text style={styles.comment}>{review.comment}</Text>
                </>
              ) : (
                <Text>Aucun rapport trouvé.</Text>
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
    maxHeight: '90%',
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 12,
  },
  label: {
    fontWeight: '600',
    marginTop: 12,
  },
  value: {
    marginBottom: 8,
  },
  table: {
    marginTop: 8,
    marginBottom: 16,
  },
  skillRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  skillName: {
    flex: 2,
    fontWeight: '500',
  },
  skillPts: {
    flex: 1,
    textAlign: 'center',
  },
  skillStatus: {
    flex: 1,
    textAlign: 'right',
  },
  comment: {
    fontStyle: 'italic',
    marginTop: 4,
    color: '#444',
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
});

export default ReviewModal;
