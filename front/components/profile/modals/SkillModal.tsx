import React, { useEffect, useState } from 'react';
import {
  Modal,
  View,
  Text,
  StyleSheet,
  FlatList,
  Pressable,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';

interface Props {
  visible: boolean;
  skillId: number | null;
  skillName: string;
  onClose: () => void;
}

export default function SkillJobModal({
  visible,
  skillId,
  skillName,
  onClose,
}: Props) {
  const [jobs, setJobs] = useState<any[]>([]);

  useEffect(() => {
    if (!skillId) return;

    const fetchJobs = async () => {
      const res = await ApiActions.get({
        route: 'student/skill',
        params: {
          skill_id: skillId,
          job_name: '',
          status: '',
          job_skill_points: '',
          job_skill_earned: '',
          group_name: '',
        },
      });

      if (res?.data) {
        setJobs(res.data);
      }
    };

    fetchJobs();
  }, [skillId]);

  const renderItem = ({ item }: { item: any }) => (
    <View style={styles.row}>
      <Text style={[styles.cell, styles.bold]}>{item.job_name}</Text>
      <Text style={styles.cell}>{item.skill_status}</Text>
      <Text style={styles.cell}>{item.job_skill_earned}</Text>
      <Text style={styles.cell}>{item.group_name || '-'}</Text>
    </View>
  );

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.overlay}>
        <View style={styles.modal}>
          <Text style={styles.title}>
            Détail de la compétence [{skillName}]
          </Text>

          <View style={styles.headerRow}>
            <Text style={[styles.header, styles.bold]}>Projet</Text>
            <Text style={styles.header}>Status</Text>
            <Text style={styles.header}>Gain</Text>
            <Text style={styles.header}>Groupe</Text>
          </View>

          <FlatList
            data={jobs}
            keyExtractor={(item, i) => `${item.job_id}-${i}`}
            renderItem={renderItem}
          />

          <Pressable style={styles.closeBtn} onPress={onClose}>
            <Text style={styles.closeText}>Fermer</Text>
          </Pressable>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'center',
    padding: 16,
  },
  modal: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 16,
    maxHeight: '90%',
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#0084FA',
    marginBottom: 12,
    textAlign: 'center',
  },
  headerRow: {
    flexDirection: 'row',
    marginBottom: 6,
  },
  header: {
    flex: 1,
    fontWeight: 'bold',
    color: '#555',
  },
  row: {
    flexDirection: 'row',
    paddingVertical: 6,
    borderBottomColor: '#eee',
    borderBottomWidth: 1,
  },
  cell: {
    flex: 1,
    color: '#333',
  },
  bold: {
    fontWeight: '600',
  },
  closeBtn: {
    marginTop: 16,
    backgroundColor: '#0084FA',
    paddingVertical: 10,
    borderRadius: 6,
  },
  closeText: {
    color: '#fff',
    textAlign: 'center',
    fontWeight: 'bold',
  },
});
