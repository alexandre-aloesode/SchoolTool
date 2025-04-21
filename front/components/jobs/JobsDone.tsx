import React, { useEffect, useState } from "react";
import { View, Text, FlatList, StyleSheet } from "react-native";
import { Picker } from "@react-native-picker/picker";
import { IconButton } from "react-native-paper";
import { ApiActions } from "@/services/ApiServices";

const JobsDone = () => {
  const [promotions, setPromotions] = useState([]);
  const [units, setUnits] = useState([]);
  const [jobsDone, setJobsDone] = useState([]);

  const [selectedPromotion, setSelectedPromotion] = useState("");
  const [selectedUnit, setSelectedUnit] = useState("all");

  useEffect(() => {
    fetchInitialData();
  }, []);

  useEffect(() => {
    if (selectedPromotion) {
      loadUnitsAndJobs();
    }
  }, [selectedPromotion]);

  useEffect(() => {
    if (selectedPromotion) {
      filterJobs();
    }
  }, [selectedUnit]);

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
        job_unit_id: allUnitIds,
        order: "click_date",
      },
    });
  
    setJobsDone(jobsResponse.data);
  };
  

  const filterJobs = () => {
    if (selectedUnit === "all") return jobsDone;
    return jobsDone.filter((job) => job.job_unit_id === selectedUnit);
  };

  const renderHeader = () => (
    <View style={[styles.row, styles.headerRow]}>
      <Text style={[styles.columnTitle, { flex: 1 }]}>Nom</Text>
      <View style={[styles.selectors, { flex: 2 }]}>
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
  );

  const renderJob = ({ item }) => {
    if (selectedUnit !== "all" && item.job_unit_id !== selectedUnit)
      return null;

    return (
      <View style={styles.row}>
        <Text style={[styles.jobTitle, { flex: 1 }]}>{item.job_name}</Text>
        <View style={[styles.jobDetails, { flex: 2 }]}>
          <Text style={styles.unitText}>{item.job_unit_name}</Text>
          <IconButton
            icon="magnify"
            size={20}
            onPress={() => console.log("Details", item)}
          />
        </View>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.sectionTitle}>Projets finis</Text>
      {renderHeader()}
      <FlatList
        data={filterJobs()}
        renderItem={renderJob}
        keyExtractor={(item, index) =>
          item?.registration_id?.toString?.() || `job-${index}`
        }
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
    paddingBottom: 8,
    marginBottom: 8,
    flexDirection: "row",
    alignItems: "center",
  },
  columnTitle: {
    fontSize: 14,
    fontWeight: "bold",
    color: "#333",
  },
  selectors: {
    flexDirection: "row",
    gap: 8,
  },
  picker: {
    flex: 1,
    height: 32,
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
});

export default JobsDone;
