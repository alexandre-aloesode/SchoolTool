import React, { useContext, useEffect } from "react";
import { StyleSheet, View, Text, Button } from "react-native";
import LoginWithGoogle from "@/components/auth/GoogleAuth";
import AuthContext from "@/context/authContext";

export default function JobsMain() {
  const auth = useContext(AuthContext);
  console.log("userData", auth);
  
  return (
    <View style={styles.container}>
      {auth?.user ? (
        <>
          <Text style={styles.title}>Bienvenue !</Text>
          <Button title="Se déconnecter" onPress={auth.logout} color="red" />
        </>
      ) : (
        <LoginWithGoogle />
      )}
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
