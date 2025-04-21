import React, { useEffect, useState } from "react";
import {
  View,
  Text,
  FlatList,
  StyleSheet,
  Modal,
  Dimensions,
  Pressable,
  ScrollView,
} from "react-native";
import { Picker } from "@react-native-picker/picker";
import { IconButton, Button } from "react-native-paper";
import { ApiActions } from "@/services/ApiServices";

const screenWidth = Dimensions.get("window").width;

const JobsDone = () => {
  const [promotions, setPromotions] = useState([]);
  const [units, setUnits] = useState([]);
  const [jobsDone, setJobsDone] = useState([]);

  const [selectedPromotion, setSelectedPromotion] = useState("");
  const [selectedUnit, setSelectedUnit] = useState("all");

  const [selectedJob, setSelectedJob] = useState(null);
  const [modalVisible, setModalVisible] = useState(false);

  useEffect(() => {
    fetchInitialData();
  }, []);

  useEffect(() => {
    if (selectedPromotion) {
      loadUnitsAndJobs();
    }
  }, [selectedPromotion]);

  const fetchInitialData = async () => {
    const history = await ApiActions.get({
      route: "promotion/history",
      params: {
        promotion_id: "",
        promotion_name: "",
      },
    });

    setPromotions(history.data);
    setSelectedPromotion(history.data[0]?.promotion_id || "");
  };

  const loadUnitsAndJobs = async () => {
    const unitResponse = await ApiActions.get({
      route: "promotion/unit",
      params: {
        promotion_id: selectedPromotion,
        unit_id: "",
        unit_name: "",
      },
    });

    const allUnits = unitResponse.data;
    setUnits(allUnits);

    const allUnitIds = allUnits.map((u) => u.unit_id);

    const jobsResponse = await ApiActions.get({
      route: "job/done",
      params: {
        job_name: "",
        registration_id: "",
        job_unit_name: "",
        job_unit_id: allUnitIds,
        order: "click_date",
        desc: "",
      },
    });

    setJobsDone(jobsResponse.data);
  };

  const filteredJobs = () => {
    if (selectedUnit === "all") return jobsDone;
    return jobsDone.filter((job) => job.job_unit_id === selectedUnit);
  };

  const openJobModal = (job) => {
    setSelectedJob(job);
    setModalVisible(true);
  };

  const renderJob = ({ item }) => (
    <View style={styles.row}>
      <Text style={[styles.jobTitle, { flex: 1 }]}>{item.job_name}</Text>
      <View style={[styles.jobDetails, { flex: 2 }]}>
        <Text style={styles.unitText}>{item.job_unit_name}</Text>
        <IconButton icon="magnify" size={20} onPress={() => openJobModal(item)} />
      </View>
    </View>
  );

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Projets finis</Text>

      {/* Selectors */}
      <View style={styles.selectorsContainer}>
        <View style={styles.pickerWrapper}>
          <Text style={styles.pickerLabel}>Promotion</Text>
          <Picker
            selectedValue={selectedPromotion}
            onValueChange={(val) => {
              setSelectedPromotion(val);
              setSelectedUnit("all");
            }}
            style={styles.picker}
          >
            {promotions.map((promo) => (
              <Picker.Item
                label={promo.promotion_name}
                value={promo.promotion_id}
                key={promo.promotion_id}
              />
            ))}
          </Picker>
        </View>

        <View style={styles.pickerWrapper}>
          <Text style={styles.pickerLabel}>Unité</Text>
          <Picker
            selectedValue={selectedUnit}
            onValueChange={(val) => setSelectedUnit(val)}
            style={styles.picker}
          >
            <Picker.Item label="Toutes les units" value="all" key="all" />
            {units.map((unit) => (
              <Picker.Item
                label={unit.unit_name}
                value={unit.unit_id}
                key={unit.unit_id}
              />
            ))}
          </Picker>
        </View>
      </View>

      {/* Scrollable Jobs List */}
      <View style={styles.listWrapper}>
        <FlatList
          data={filteredJobs()}
          renderItem={renderJob}
          keyExtractor={(item, index) =>
            item?.registration_id?.toString?.() || `job-${index}`
          }
        />
      </View>

      {/* Modal Popup */}
      <Modal animationType="slide" transparent={true} visible={modalVisible}>
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <ScrollView>
              <Text style={styles.modalTitle}>
                [{selectedJob?.job_unit_name}] {selectedJob?.job_name}
              </Text>

              <Text style={styles.modalSubtitle}>{selectedJob?.group_name || selectedJob?.job_code}</Text>
              <Text style={styles.modalDescription}>
                {selectedJob?.job_description || "Aucune description disponible."}
              </Text>

              <Text style={styles.modalInfo}>👨‍🏫 Chef de groupe : {selectedJob?.lead_email}</Text>
              <Text style={styles.modalInfo}>📅 Début : {selectedJob?.start_date}</Text>
              <Text style={styles.modalInfo}>📅 Fin : {selectedJob?.end_date}</Text>

              <Button
                mode="contained"
                onPress={() => setModalVisible(false)}
                style={{ marginTop: 16 }}
              >
                Fermer
              </Button>
            </ScrollView>
          </View>
        </View>
      </Modal>
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
    flex: 1,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: "bold",
    color: "#e91e63",
    marginBottom: 12,
  },
  selectorsContainer: {
    flexDirection: screenWidth < 500 ? "column" : "row",
    gap: 12,
    marginBottom: 12,
  },
  pickerWrapper: {
    flex: 1,
  },
  pickerLabel: {
    fontSize: 13,
    fontWeight: "600",
    color: "#555",
    marginBottom: 4,
  },
  picker: {
    height: 42,
    backgroundColor: "#f9f9f9",
    borderColor: "#ccc",
    borderWidth: 1,
    borderRadius: 4,
  },
  listWrapper: {
    flex: 1,
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
    fontWeight: "bold",
    color: "#111",
  },
  unitText: {
    fontSize: 13,
    color: "#444",
  },
  jobDetails: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },

  // Modal
  modalContainer: {
    flex: 1,
    backgroundColor: "rgba(0,0,0,0.5)",
    justifyContent: "center",
    padding: 16,
  },
  modalContent: {
    backgroundColor: "white",
    borderRadius: 12,
    padding: 20,
    elevation: 5,
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: "bold",
    color: "#e91e63",
    marginBottom: 8,
  },
  modalSubtitle: {
    fontSize: 14,
    fontWeight: "600",
    marginBottom: 6,
  },
  modalDescription: {
    fontSize: 13,
    marginBottom: 8,
    color: "#333",
  },
  modalInfo: {
    fontSize: 13,
    marginBottom: 4,
    color: "#555",
  },
});

export default JobsDone;
