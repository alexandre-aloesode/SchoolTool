import React, { useEffect, useState } from 'react';
import {
  Modal,
  View,
  Text,
  StyleSheet,
  FlatList,
  Pressable,
  TouchableOpacity,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';
import SkillDetailModal from './SkillModal';

interface Props {
  visible: boolean;
  classId: number | null;
  skillName: string;
  onClose: () => void;
}

export default function SkillCategoryModal({ visible, classId, skillName, onClose }: Props) {
  const [skills, setSkills] = useState<any[]>([]);
  const [selectedSkill, setSelectedSkill] = useState<{ id: number; name: string } | null>(null);

  useEffect(() => {
    if (!classId) return;

    const fetchSkills = async () => {
      const res = await ApiActions.get({
        route: 'student/skill/total',
        params: {
          class_id: classId,
          skill_id: '',
          skill_name: '',
          grade: '',
          class_name: '',
        },
      });

      if (res?.data) {
        setSkills(res.data);
      }
    };

    fetchSkills();
  }, [classId]);

  const renderItem = ({ item }: { item: any }) => (
    <TouchableOpacity
      style={styles.row}
      onPress={() => setSelectedSkill({ id: item.skill_id, name: item.skill_name })}
    >
      <Text style={[styles.cell, styles.name]}>{item.skill_name}</Text>
      <Text style={styles.cell}>{item.grade}</Text>
      <Text style={styles.cell}>{item.earned}</Text>
      <Text style={styles.cell}>{item.progress}</Text>
    </TouchableOpacity>
  );

  return (
    <>
      <Modal visible={visible} animationType="slide" transparent>
        <View style={styles.overlay}>
          <View style={styles.modal}>
            <Text style={styles.title}>Compétences [{skillName}]</Text>

            <View style={styles.headerRow}>
              <Text style={[styles.header, styles.name]}>Nom</Text>
              <Text style={styles.header}>Niveau</Text>
              <Text style={styles.header}>Acquis</Text>
              <Text style={styles.header}>En cours</Text>
            </View>

            <FlatList
              data={skills}
              keyExtractor={(item, i) => `${item.skill_id}-${i}`}
              renderItem={renderItem}
            />

            <Pressable style={styles.closeBtn} onPress={onClose}>
              <Text style={styles.closeText}>Fermer</Text>
            </Pressable>
          </View>
        </View>
      </Modal>

      <SkillDetailModal
        visible={!!selectedSkill}
        skillId={selectedSkill?.id || null}
        skillName={selectedSkill?.name || ''}
        onClose={() => setSelectedSkill(null)}
      />
    </>
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
    color: '#e91e63',
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
  name: {
    flex: 2,
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
