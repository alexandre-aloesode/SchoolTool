import React, { useContext } from "react";
import { StyleSheet, View, Text, Button } from "react-native";
import LoginWithGoogle from "@/components/auth/GoogleAuth";
import AuthContext from "@/context/authContext";
import LogtimeChart from "@/components/dashboard/logtimes";
import GoogleCalendarWidget from "@/components/dashboard/googleCalendar";

export default function HomeScreen() {
  const auth = useContext(AuthContext);

  return (
    <View style={styles.container}>
      {auth?.user ? (
        <>
          <Text style={styles.title}>Bienvenue !</Text>
          <Button title="Se déconnecter" onPress={auth.logout} color="red" />
          <LogtimeChart />
          <GoogleCalendarWidget />
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
