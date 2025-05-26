import React, { useEffect, useState } from "react";
import { View, Text, FlatList, StyleSheet, Pressable } from "react-native";
import { ProgressBar } from "react-native-paper";
import { ApiActions } from "@/services/ApiServices";
import ProgressModal from "./modals/ProgressModal";

const JobsInProgress = () => {
  const [jobsInProgress, setJobsInProgress] = useState([]);
  const [selectedJob, setSelectedJob] = useState(null);

  const getJobsInProgress = async () => {
    const jobsRequest = await ApiActions.get({
      route: "job/progress",
      params: {
        job_id: "",
        job_name: "",
        start_date: "",
        end_date: "",
        registration_id: "",
        group_id: "",
        job_is_done: "",
      },
    });
    if (jobsRequest) {
      setJobsInProgress(jobsRequest.data);
    }
  };

  useEffect(() => {
    getJobsInProgress();
  }, []);

  const getProgressInfo = (job) => {
    const startDate = new Date(job.start_date.replace(" ", "T"));
    const endDate = new Date(job.end_date.replace(" ", "T"));
    const today = new Date();

    const duration = (endDate - startDate) / (1000 * 60 * 60 * 24);
    const remaining = (endDate - today) / (1000 * 60 * 60 * 24);
    const delay = Math.max(0, -remaining);
    const progress = Math.min(
      1,
      Math.max(0, (duration - remaining) / duration)
    );

    return { progress, delay: Math.round(delay), isLate: delay > 0 };
  };

  const renderHeader = () => (
    <View style={[styles.row, styles.headerRow]}>
      <Text style={[styles.columnTitle, { flex: 1 }]}>Nom</Text>
      <Text style={[styles.columnTitle, { flex: 3 }]}>Progression</Text>
    </View>
  );

  const renderJob = ({ item }) => {
    const { progress, delay, isLate } = getProgressInfo(item);

    return (
      <Pressable style={styles.row} onPress={() => setSelectedJob(item)}>
        <Text style={[styles.jobTitle, { flex: 1 }]}>{item.job_name}</Text>
        <View style={[styles.progressContainer, { flex: 3 }]}>
          <View style={styles.barAndLabel}>
            <ProgressBar
              progress={progress}
              color="#0097a7"
              style={styles.progress}
            />
            {isLate && (
              <Text style={styles.lateLabel}>Retard de {delay} jours</Text>
            )}
          </View>
        </View>
      </Pressable>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Projets en cours</Text>
      {renderHeader()}
      <FlatList
        data={jobsInProgress}
        renderItem={renderJob}
        keyExtractor={(item, index) =>
          item?.job_id?.toString?.() || `job-${index}`
        }
      />

      <ProgressModal
        visible={!!selectedJob}
        job={selectedJob}
        onClose={() => setSelectedJob(null)}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    borderRadius: 8,
    padding: 16,
    backgroundColor: "white",
    margin: 16,
    borderColor: "#ccc",
    borderWidth: 1,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: "bold",
    color: "#e91e63",
    marginBottom: 12,
  },
  headerRow: {
    borderBottomWidth: 1,
    borderBottomColor: "#ddd",
    paddingBottom: 4,
    marginBottom: 4,
  },
  columnTitle: {
    fontSize: 14,
    fontWeight: "bold",
    color: "#333",
  },
  row: {
    flexDirection: "row",
    alignItems: "center",
    paddingVertical: 10,
    borderBottomColor: "#eee",
    borderBottomWidth: 1,
  },
  jobTitle: {
    fontSize: 14,
    color: "#111",
  },
  progressContainer: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
  },
  barAndLabel: {
    flex: 1,
  },
  progress: {
    height: 10,
    borderRadius: 4,
    backgroundColor: "#e0e0e0",
  },
  lateLabel: {
    fontSize: 12,
    color: "#007bff",
    position: "absolute",
    top: -16,
    right: 0,
  },
});

export default JobsInProgress;
