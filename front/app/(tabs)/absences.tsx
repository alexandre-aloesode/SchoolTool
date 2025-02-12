import React, { useState, useRef } from "react";
import {
  View,
  Text,
  TextInput,
  Button,
  StyleSheet,
  Alert,
  TouchableOpacity,
  ActivityIndicator,
  Modal,
  TouchableWithoutFeedback,
} from "react-native";
import DateTimePicker from "@react-native-community/datetimepicker";
import * as ImagePicker from "expo-image-picker";
import { format } from "date-fns";
import { ApiActions } from "../../services/ApiServices";

interface AbsenceForm {
  start_date: string;
  end_date: string;
  duration: number;
  reason: string;
  image: string | null;
  imageName: string;
}

const UploadAbsences: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const [absenceForm, setAbsenceForm] = useState<AbsenceForm>({
    start_date: "",
    end_date: "",
    duration: 0,
    reason: "",
    image: null,
    imageName: "",
  });
  const [showDatePicker, setShowDatePicker] = useState<"start_date" | "end_date" | null>(null);

  const reasons = [
    "Accident de transport",
    "Maladie",
    "Raison familiale",
    "Evènement entreprise",
    "Autre",
  ];

  const handleDateChange = (field: "start_date" | "end_date", selectedDate: Date | undefined) => {
    if (selectedDate) {
      setAbsenceForm((prev) => ({
        ...prev,
        [field]: selectedDate.toISOString().split("T")[0],
      }));
    }
    setShowDatePicker(null); // Close the picker after selection
  };

  const handleReasonChange = (reason: string) => {
    setAbsenceForm((prev) => ({ ...prev, reason }));
  };

  const handleImagePick = async () => {
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 1,
    });

    if (!result.canceled) {
      setAbsenceForm((prev) => ({
        ...prev,
        image: result.assets[0].uri,
        imageName: result.assets[0].uri.split("/").pop() || "",
      }));
    }
  };

  const recapAbsence = () => {
    return `
      Raison : ${absenceForm.reason}

      Du ${format(new Date(absenceForm.start_date), "dd/MM/yyyy")} au ${format(
      new Date(absenceForm.end_date),
      "dd/MM/yyyy"
    )}

      Durée : ${absenceForm.duration} ${
      absenceForm.duration > 1 ? "jours ouvrés" : "jour ouvré"
    }`;
  };

  const handleUploadAbsence = () => {
    if (
      !absenceForm.start_date ||
      !absenceForm.end_date ||
      !absenceForm.reason ||
      !absenceForm.image
    ) {
      Alert.alert("Erreur", "Veuillez remplir tous les champs");
      return;
    }

    const startDate = new Date(absenceForm.start_date);
    const endDate = new Date(absenceForm.end_date);

    if (startDate >= endDate) {
      Alert.alert("Erreur", "La date de début doit être antérieure à la date de retour");
      return;
    }

    let duration = 0;
    let tempDate = new Date(startDate);

    while (tempDate < endDate) {
      if (tempDate.getDay() !== 0 && tempDate.getDay() !== 6) {
        duration++;
      }
      tempDate.setDate(tempDate.getDate() + 1);
    }

    setAbsenceForm((prev) => ({ ...prev, duration }));

    Alert.alert("Confirmation", recapAbsence(), [
      { text: "Annuler", style: "cancel" },
      {
        text: "Confirmer",
        onPress: async () => {
          setLoading(true);
          try {
            const response = await ApiActions.post({
              route: "uploadAbsence",
              params: absenceForm,
            });
            if (response.status === 200) {
              setLoading(false);
              Alert.alert("Succès", "Absence envoyée avec succès");
              setAbsenceForm({
                start_date: "",
                end_date: "",
                reason: "",
                image: null,
                imageName: "",
                duration: 0,
              });
            } else {
              throw new Error();
            }
          } catch (error) {
            setLoading(false);
            Alert.alert("Erreur", "Une erreur est survenue");
          }
        },
      },
    ]);
  };

  return (
    <View style={styles.container}>
      {!loading ? (
        <View style={styles.form}>
          <Text style={styles.label}>Date de début</Text>
          <TouchableOpacity onPress={() => setShowDatePicker("start_date")}>
            <Text>{absenceForm.start_date || "Sélectionner une date"}</Text>
          </TouchableOpacity>

          <Text style={styles.label}>Date de retour</Text>
          <TouchableOpacity onPress={() => setShowDatePicker("end_date")}>
            <Text>{absenceForm.end_date || "Sélectionner une date"}</Text>
          </TouchableOpacity>

          <Text style={styles.label}>Motif</Text>
          {reasons.map((reason, index) => (
            <TouchableOpacity key={index} onPress={() => handleReasonChange(reason)}>
              <Text style={styles.reason}>{reason}</Text>
            </TouchableOpacity>
          ))}

          <TouchableOpacity onPress={handleImagePick} style={styles.uploadButton}>
            <Text>Uploader un justificatif</Text>
          </TouchableOpacity>
          {absenceForm.imageName && (
            <Text style={styles.imageName}>Fichier sélectionné : {absenceForm.imageName}</Text>
          )}

          <Button title="Valider" onPress={handleUploadAbsence} />
        </View>
      ) : (
        <ActivityIndicator size="large" color="#0828A7" />
      )}

      {showDatePicker && (
        <Modal
          transparent={true}
          visible={true}
          animationType="slide"
          onRequestClose={() => setShowDatePicker(null)}
        >
          <TouchableWithoutFeedback onPress={() => setShowDatePicker(null)}>
            <View style={styles.modalBackground}>
              <DateTimePicker
                value={new Date(absenceForm[showDatePicker] || Date.now())}
                mode="date"
                display="default"
                onChange={(event, date) => handleDateChange(showDatePicker, date)}
              />
            </View>
          </TouchableWithoutFeedback>
        </Modal>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "#f9f9f9",
  },
  form: {
    width: "90%",
    padding: 20,
    backgroundColor: "#fff",
    borderRadius: 10,
    elevation: 5,
  },
  label: {
    fontSize: 16,
    marginVertical: 10,
  },
  reason: {
    padding: 10,
    backgroundColor: "#f0f0f0",
    borderRadius: 5,
    marginVertical: 5,
  },
  uploadButton: {
    padding: 15,
    backgroundColor: "#ddd",
    borderRadius: 5,
    alignItems: "center",
    marginVertical: 10,
  },
  imageName: {
    fontSize: 14,
    marginVertical: 10,
  },
  modalBackground: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "rgba(0, 0, 0, 0.5)",
  },
});

export default UploadAbsences;
