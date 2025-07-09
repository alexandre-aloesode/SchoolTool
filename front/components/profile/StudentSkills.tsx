import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  Pressable,
  Platform,
} from 'react-native';
import { Svg } from 'react-native-svg';
import { ApiActions } from '@/services/ApiServices';

const { VictoryChart, VictoryPolarAxis, VictoryArea } =
  Platform.OS === 'web' ? require('victory') : require('victory-native');

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
            }))
            .sort((a: { skill: string }, b: { skill: string }) => a.skill.localeCompare(b.skill))
            .map((item: { class_name: string; earned: string; grade: string; total: string }, index: number) => ({
              ...item,
              index,
            }));

          setSkillData(formatted);
        }
      } catch (err) {
        console.error('Erreur chargement compétences', err);
      }
    };

    loadSkills();
  }, []);

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Compétences</Text>

      {skillData.length > 0 && (
        <Svg width={350} height={350}>
          <VictoryChart
            polar
            standalone={false}
            domain={{ y: [0, 120] }}
            width={350}
            height={350}
          >
            <VictoryPolarAxis
              tickValues={skillData.map((s) => s.index)}
              tickFormat={skillData.map((s) => s.skill)}
              style={{
                tickLabels: {
                  fontSize: 10,
                  fill: 'black',
                },
                axis: { stroke: '#e0e0e0' },
                grid: { stroke: '#e0e0e0', strokeDasharray: '4,8' },
              }}
              events={[
                {
                  target: 'tickLabels',
                  eventHandlers: {
                    onPressIn: (_: any, props: { index: number }) => {
                      const clicked = skillData.find(
                        (s) => s.index === props.index,
                      );
                      setSelectedSkill(clicked);
                      return [];
                    },
                  },
                },
              ]}
            />

            <VictoryArea
              data={skillData}
              x="index"
              y="value"
              style={{
                data: {
                  fill: 'rgba(255,0,100,0.3)',
                  stroke: 'deeppink',
                  strokeWidth: 2,
                },
              }}
            />
          </VictoryChart>
        </Svg>
      )}

      <Modal visible={!!selectedSkill} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <Text style={styles.modalTitle}>{selectedSkill?.skill}</Text>
            <Text style={styles.modalText}>
              Grade : {selectedSkill?.grade || 'N/A'}
            </Text>
            <Text style={styles.modalText}>
              Score : {selectedSkill?.value} / {selectedSkill?.total}
            </Text>
            <Pressable
              onPress={() => setSelectedSkill(null)}
              style={styles.closeButton}
            >
              <Text style={styles.closeButtonText}>Fermer</Text>
            </Pressable>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 20,
    alignItems: 'center',
    backgroundColor: 'white',
    flex: 1,
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#e91e63',
    marginBottom: 10,
  },
  modalOverlay: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: 'rgba(0,0,0,0.5)',
    padding: 20,
  },
  modalContainer: {
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 20,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  modalText: {
    fontSize: 16,
    marginBottom: 10,
  },
  closeButton: {
    backgroundColor: '#e91e63',
    padding: 10,
    borderRadius: 6,
    alignItems: 'center',
  },
  closeButtonText: {
    color: 'white',
    fontWeight: 'bold',
  },
});
