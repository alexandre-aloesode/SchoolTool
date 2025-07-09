import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Dimensions,
  ScrollView,
  Modal,
  Pressable,
} from 'react-native';
import dayjs from 'dayjs';
import { useAuth } from '@/hooks/useAuth';
import { getValidGoogleAccessToken } from '@/utils/googleToken';
import type { GoogleCalendarEvent } from '@/types/googleCalendarTypes';
import type { Dayjs } from 'dayjs';

const screenWidth = Dimensions.get('window').width;
const columnMinWidth = 130;

const GoogleCalendarWidget = () => {
  const { user } = useAuth();
  const [events, setEvents] = useState<GoogleCalendarEvent[]>([]);
  const [selectedEvent, setSelectedEvent] = useState<GoogleCalendarEvent | null>(null);
  const [startOfWeek, setStartOfWeek] = useState(
    dayjs().startOf('week').add(1, 'day'),
  );

  const fetchEvents = async () => {
    if (!user) return;

    const token = await getValidGoogleAccessToken();
    if (!token) return;

    try {
      const endOfWeek = startOfWeek.add(6, 'day').endOf('day');
      const response = await fetch(
        `https://www.googleapis.com/calendar/v3/calendars/primary/events?timeMin=${startOfWeek.toISOString()}&timeMax=${endOfWeek.toISOString()}&singleEvents=true&orderBy=startTime`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        },
      );
      const data = await response.json();
      setEvents(data.items || []);
    } catch (err) {
      console.error(
        'Erreur de récupération des événements Google Calendar',
        err,
      );
    }
  };

  useEffect(() => {
    fetchEvents();
  }, [startOfWeek]);

  const days = Array.from({ length: 7 }).map((_, i) =>
    startOfWeek.add(i, 'day'),
  );

  const groupedEvents = days.map((day) => {
    const dayEvents = events.filter((event) =>
      dayjs(event.start?.dateTime || event.start?.date).isSame(day, 'day'),
    );
    return { day, events: dayEvents };
  });

  const isToday = (date: Dayjs) => dayjs().isSame(date, 'day');

  return (
    <View style={styles.wrapper}>
      <View style={styles.nav}>
        <Text
          style={styles.arrow}
          onPress={() => setStartOfWeek((prev) => prev.subtract(7, 'day'))}
        >
          ◀
        </Text>
        <Text style={styles.weekText}>
          Semaine du {startOfWeek.format('DD/MM/YYYY')}
        </Text>
        <Text
          style={styles.arrow}
          onPress={() => setStartOfWeek((prev) => prev.add(7, 'day'))}
        >
          ▶
        </Text>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={[
          styles.grid,
          {
            width: Math.max(columnMinWidth * groupedEvents.length, screenWidth),
          },
        ]}
      >
        {groupedEvents.map(({ day, events }, index) => (
          <View
            key={index}
            style={[styles.column, isToday(day) && styles.today]}
          >
            <Text style={styles.dayTitle}>{day.format('ddd DD/MM')}</Text>
            {events.length > 0 ? (
              events.map((event) => (
                <Pressable
                  key={event.id}
                  onPress={() => setSelectedEvent(event)}
                  style={styles.eventCard}
                >
                  <Text style={styles.eventTitle}>{event.summary}</Text>
                  <Text style={styles.eventTime}>
                    {dayjs(event.start.dateTime || event.start.date).format(
                      'HH:mm',
                    )}
                  </Text>
                </Pressable>
              ))
            ) : (
              <Text style={styles.noEvent}>—</Text>
            )}
          </View>
        ))}
      </ScrollView>

      <Modal
        visible={!!selectedEvent}
        animationType="slide"
        transparent
        onRequestClose={() => setSelectedEvent(null)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>{selectedEvent?.summary}</Text>
            <Text>
              {dayjs(
                selectedEvent?.start.dateTime || selectedEvent?.start.date,
              ).format('dddd DD MMMM YYYY • HH:mm')}
            </Text>
            {selectedEvent?.description && (
              <Text style={styles.modalDescription}>
                {selectedEvent.description}
              </Text>
            )}
            <Pressable
              onPress={() => setSelectedEvent(null)}
              style={styles.modalClose}
            >
              <Text style={styles.modalCloseText}>Fermer</Text>
            </Pressable>
          </View>
        </View>
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: {
    marginTop: 10,
    marginBottom: 20,
    width: '90%',
    borderRadius: 12,
    backgroundColor: '#fff',
    paddingVertical: 10,
    paddingHorizontal: 6,
    minHeight: 310,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  nav: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    alignItems: 'center',
    marginBottom: 10,
  },
  arrow: {
    fontSize: 20,
    color: '#1188aa',
  },
  weekText: {
    fontWeight: 'bold',
    color: '#1188aa',
  },
  grid: {
    flexDirection: 'row',
    paddingHorizontal: 8,
  },
  column: {
    minWidth: 130,
    padding: 8,
    backgroundColor: '#f9f9f9',
    borderRadius: 8,
    marginHorizontal: 4,
    alignItems: 'center',
  },
  today: {
    borderWidth: 2,
    borderColor: '#1188aa',
  },
  dayTitle: {
    fontWeight: 'bold',
    marginBottom: 6,
    color: '#333',
  },
  eventCard: {
    backgroundColor: '#e5f3fc',
    padding: 6,
    borderRadius: 4,
    marginBottom: 4,
    width: '100%',
    alignItems: 'center',
  },
  eventTitle: {
    fontWeight: '500',
    fontSize: 12,
    textAlign: 'center',
  },
  eventTime: {
    fontSize: 11,
    color: '#666',
  },
  noEvent: {
    fontStyle: 'italic',
    color: '#aaa',
  },

  // Modal
  modalOverlay: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0,0,0,0.5)',
  },
  modalContent: {
    width: '85%',
    backgroundColor: '#fff',
    padding: 20,
    borderRadius: 10,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  modalDescription: {
    marginTop: 10,
    color: '#444',
  },
  modalClose: {
    marginTop: 20,
    alignSelf: 'center',
    padding: 10,
    backgroundColor: '#1188aa',
    borderRadius: 6,
  },
  modalCloseText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});

export default GoogleCalendarWidget;
