import React, { useContext, useEffect, useState } from "react";
import { View, Text, StyleSheet, Dimensions, ScrollView } from "react-native";
import dayjs from "dayjs";
import AuthContext from "@/context/authContext";

const screenWidth = Dimensions.get("window").width;
const columnMinWidth = 130;

const GoogleCalendarWidget = () => {
  const { user } = useContext(AuthContext);
  const [events, setEvents] = useState([]);
  const [startOfWeek, setStartOfWeek] = useState(dayjs().startOf("week").add(1, "day")); // Lundi

  const fetchEvents = async () => {
    try {
      const endOfWeek = startOfWeek.add(6, "day").endOf("day");
      const response = await fetch(
        `https://www.googleapis.com/calendar/v3/calendars/primary/events?timeMin=${startOfWeek.toISOString()}&timeMax=${endOfWeek.toISOString()}&singleEvents=true&orderBy=startTime`,
        {
          headers: {
            Authorization: `Bearer ${user?.googleAccessToken}`,
          },
        }
      );
      const data = await response.json();
      setEvents(data.items || []);
    } catch (err) {
      console.error("Erreur de récupération des événements Google Calendar", err);
    }
  };

  useEffect(() => {
    if (user?.googleAccessToken) fetchEvents();
  }, [startOfWeek]);

  const days = Array.from({ length: 7 }).map((_, i) => startOfWeek.add(i, "day"));

  const groupedEvents = days.map((day) => {
    const dayEvents = events.filter((event) =>
      dayjs(event.start?.dateTime || event.start?.date).isSame(day, "day")
    );
    return { day, events: dayEvents };
  });

  return (
    <View style={styles.wrapper}>
      <View style={styles.nav}>
        <Text style={styles.arrow} onPress={() => setStartOfWeek(prev => prev.subtract(7, "day"))}>◀</Text>
        <Text style={styles.weekText}>Semaine du {startOfWeek.format("DD/MM/YYYY")}</Text>
        <Text style={styles.arrow} onPress={() => setStartOfWeek(prev => prev.add(7, "day"))}>▶</Text>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={[
          styles.grid,
          { width: Math.max(columnMinWidth * groupedEvents.length, screenWidth) },
        ]}
      >
        {groupedEvents.map(({ day, events }, index) => (
          <View key={index} style={styles.column}>
            <Text style={styles.dayTitle}>{day.format("ddd DD/MM")}</Text>
            {events.length > 0 ? (
              events.map((event) => (
                <View key={event.id} style={styles.eventCard}>
                  <Text style={styles.eventTitle}>{event.summary}</Text>
                  <Text style={styles.eventTime}>
                    {dayjs(event.start.dateTime || event.start.date).format("HH:mm")}
                  </Text>
                </View>
              ))
            ) : (
              <Text style={styles.noEvent}>—</Text>
            )}
          </View>
        ))}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: {
    marginTop: 20,
    marginBottom: 60,
    width: "100%",
  },
  nav: {
    flexDirection: "row",
    justifyContent: "space-between",
    paddingHorizontal: 16,
    alignItems: "center",
    marginBottom: 10,
  },
  arrow: {
    fontSize: 20,
    color: "#1188aa",
  },
  weekText: {
    fontWeight: "bold",
    color: "#1188aa",
  },
  grid: {
    flexDirection: "row",
  },
  column: {
    minWidth: columnMinWidth,
    flexGrow: 1,
    flexShrink: 0,
    padding: 8,
    backgroundColor: "#f9f9f9",
    borderRadius: 8,
    marginHorizontal: 4,
    alignItems: "center",
  },
  dayTitle: {
    fontWeight: "bold",
    marginBottom: 6,
    color: "#333",
  },
  eventCard: {
    backgroundColor: "#e5f3fc",
    padding: 6,
    borderRadius: 4,
    marginBottom: 4,
    width: "100%",
    alignItems: "center",
  },
  eventTitle: {
    fontWeight: "500",
    fontSize: 12,
    textAlign: "center",
  },
  eventTime: {
    fontSize: 11,
    color: "#666",
  },
  noEvent: {
    fontStyle: "italic",
    color: "#aaa",
  },
});

export default GoogleCalendarWidget;
