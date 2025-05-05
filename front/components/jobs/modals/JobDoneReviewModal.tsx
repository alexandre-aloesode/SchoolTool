import React, { useEffect, useState } from "react";
import {
  View,
  Text,
  StyleSheet,
  Modal,
  ScrollView,
  ActivityIndicator,
  Pressable,
} from "react-native";
import { ApiActions } from "@/services/ApiServices";

const JobDoneReviewModal = ({ visible, job, onClose }) => {
  const [review, setReview] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (visible && job) {
      fetchReview();
    }
  }, [visible, job]);

  const fetchReview = async () => {
    setLoading(true);
    try {
      const result = await ApiActions.get({
        route: "group/review",
        params: { group_id: job.group_id },
      });
      setReview(result.data);
    } catch (err) {
      console.error("Erreur chargement review", err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.modal}>
          <ScrollView>
            {loading ? (
              <ActivityIndicator size="large" color="#1188aa" />
            ) : (
              <>
                <Text style={styles.title}>Rapport de {review?.corrector}</Text>
                <Text style={styles.label}>Compétences :</Text>
                {review?.skill?.map((skill, i) => (
                  <View style={styles.skillRow} key={i}>
                    <Text style={styles.skillName}>{skill.skill_name}</Text>
                    <Text style={styles.skillValue}>{skill.job_skill_earned}</Text>
                    <Text style={styles.skillStatus}>{skill.skill_status}</Text>
                  </View>
                ))}

                <Text style={styles.label}>Commentaire :</Text>
                <Text style={styles.comment}>{review?.comment}</Text>
              </>
            )}

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
    backgroundColor: "rgba(0,0,0,0.5)",
    justifyContent: "center",
    alignItems: "center",
  },
  modal: {
    backgroundColor: "white",
    borderRadius: 12,
    padding: 20,
    width: "90%",
    maxHeight: "85%",
  },
  title: {
    fontSize: 18,
    fontWeight: "bold",
    color: "#e91e63",
    marginBottom: 10,
  },
  label: {
    marginTop: 12,
    fontWeight: "600",
    marginBottom: 4,
  },
  skillRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginBottom: 4,
  },
  skillName: {
    flex: 2,
    fontSize: 13,
  },
  skillValue: {
    flex: 1,
    textAlign: "center",
  },
  skillStatus: {
    flex: 1,
    textAlign: "right",
  },
  comment: {
    fontSize: 13,
    color: "#333",
    marginTop: 4,
  },
  closeBtn: {
    marginTop: 20,
    backgroundColor: "#1188aa",
    padding: 10,
    borderRadius: 6,
    alignItems: "center",
  },
  closeText: {
    color: "#fff",
    fontWeight: "bold",
  },
});

export default JobDoneReviewModal;
