import React, { useContext } from "react";
import { StyleSheet, View, Text, Button } from "react-native";
import AuthContext from "@/context/authContext";

export default function HomeScreen() {
  const auth = useContext(AuthContext);

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Bienvenue sur l'application</Text>
      <Button
        title="Se connecter avec Google"
        onPress={() => auth?.loginWithGoogle()}
        color="#4285F4"
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "#f7f7f7",
  },
  title: {
    fontSize: 24,
    marginBottom: 20,
    color: "#333",
  },
});
