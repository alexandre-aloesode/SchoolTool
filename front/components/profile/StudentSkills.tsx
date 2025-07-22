import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
} from 'react-native';
import { ApiActions } from '@/services/ApiServices';
import SkillCategoryModal from './modals/SkillCategoryModal';

export default function SkillScreen() {
  const [selectedSkill, setSelectedSkill] = useState<any>(null);
  const [skillData, setSkillData] = useState<any[]>([]);

  useEffect(() => {
    const loadSkills = async () => {
      try {
        const res = await ApiActions.get({
          route: 'student/class/total',
          params: { class_name: '' },
        });

        if (res?.data) {
          const formatted = res.data
            .filter((item: any) => item.class_name && item.earned)
            .map((item: any) => ({
              skill: item.class_name,
              value: Number(item.earned),
              grade: item.grade,
              total: item.total,
              class_id: item.class_id,
            }))
            .sort((a: { skill: string }, b: { skill: string }) =>
              a.skill.localeCompare(b.skill),
            );

          setSkillData(formatted);
        }
      } catch (err) {
        console.error('Erreur chargement compétences', err);
      }
    };

    loadSkills();
  }, []);

  const renderItem = ({ item }: { item: any }) => {
    const percentage =
      item.total && item.total > 0
        ? Math.min((item.value / item.total) * 100, 100)
        : 0;

    return (
      <TouchableOpacity
        style={styles.skillRow}
        onPress={() => setSelectedSkill(item)}
      >
        <Text style={styles.skillName}>{item.skill}</Text>
        <View style={styles.barWrapper}>
          <View
            style={[
              styles.bar,
              { width: `${percentage}%` },
            ]}
          />
          <Text style={styles.barValue}>{percentage.toFixed(1)}%</Text>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Compétences acquises</Text>

      <FlatList
        data={skillData}
        keyExtractor={(item, index) => `${item.skill}-${index}`}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
      />

      <SkillCategoryModal
        visible={!!selectedSkill}
        classId={selectedSkill?.class_id}
        skillName={selectedSkill?.skill}
        onClose={() => setSelectedSkill(null)}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f7f7f7',
    paddingTop: 16,
    paddingHorizontal: 12,
    width: '100%',
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#0084FA',
    marginBottom: 12,
    textAlign: 'center',
  },
  list: {
    paddingBottom: 40,
    width: '100%',
  },
  skillRow: {
    width: '100%',
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    elevation: 2,
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  skillName: {
    fontWeight: 'bold',
    fontSize: 16,
    marginBottom: 6,
    color: '#333',
  },
  barWrapper: {
    backgroundColor: '#e0e0e0',
    height: 24,
    borderRadius: 6,
    overflow: 'hidden',
    position: 'relative',
    justifyContent: 'center',
  },
  bar: {
    backgroundColor: '#3B82F6',
    height: '100%',
  },
  barValue: {
    position: 'absolute',
    alignSelf: 'center',
    width: '100%',
    textAlign: 'center',
    color: '#fff',
    fontWeight: 'bold',
  },
});
