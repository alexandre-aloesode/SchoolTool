import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  TouchableOpacity,
  Dimensions,
} from 'react-native';
import { LineChart } from 'react-native-chart-kit';
import { ApiActions } from '@/services/ApiServices';
import dayjs from 'dayjs';
import type { Logtime } from '@/types/logtimesTypes';

const screenWidth = Dimensions.get('window').width;

const Logtimes = () => {
  const [logtimes, setLogtimes] = useState<Logtime[]>([]);
  const [loading, setLoading] = useState(true);
  const [weekStart, setWeekStart] = useState(
    dayjs().startOf('week').add(1, 'day'),
  );

  const fetchLogtimes = async () => {
    setLoading(true);

    const weekEnd = weekStart.add(6, 'day');
    const logtimesResponse = await ApiActions.get({
      route: 'logtime',
      params: {
        date_after: weekStart.format('YYYY-MM-DD'),
        date_before: weekEnd.format('YYYY-MM-DD'),
        algo2: '',
        day: '',
      },
    });

    if (!logtimesResponse) {
      console.error("Erreur: aucune réponse de l'API");
      return;
    }
    
    if (logtimesResponse.status === 200) {
      setLogtimes(logtimesResponse.data || []);
    }

    setLoading(false);
  };

  useEffect(() => {
    fetchLogtimes();
  }, [weekStart]);

  const getChartData = () => {
    const labels = [];
    const data = [];

    let totalMinutes = 0;

    for (let i = 0; i < 7; i++) {
      const date = weekStart.add(i, 'day');
      labels.push(date.format('DD/MM'));

      const log = logtimes.find((log) =>
        dayjs(log.logtime_day).isSame(date, 'day'),
      );
      const minutes = log ? log.logtime_algo2 : 0;
      totalMinutes += minutes;
      data.push(minutes / 60);
    }

    return {
      labels,
      datasets: [
        {
          data,
          color: () => '#ff6384',
          strokeWidth: 2,
        },
      ],
      total: totalMinutes,
    };
  };

  const chartData = getChartData();

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Temps de log</Text>
      <View style={styles.header}>
        <TouchableOpacity
          onPress={() => setWeekStart((prev) => prev.subtract(7, 'day'))}
        >
          <Text style={styles.arrow}>◀</Text>
        </TouchableOpacity>

        <Text style={styles.date}>
          Semaine du {weekStart.format('DD/MM/YYYY')} • Total:{' '}
          {Math.floor(chartData.total / 60)}h
          {(chartData.total % 60).toString().padStart(2, '0')}
        </Text>

        <TouchableOpacity
          onPress={() => setWeekStart((prev) => prev.add(7, 'day'))}
        >
          <Text style={styles.arrow}>▶</Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#ff6384" />
      ) : (
        <LineChart
          data={{
            labels: chartData.labels,
            datasets: chartData.datasets,
          }}
          width={screenWidth - 40}
          height={220}
          chartConfig={{
            backgroundGradientFrom: '#fff',
            backgroundGradientTo: '#fff',
            color: () => '#ff6384',
            labelColor: () => '#333',
            decimalPlaces: 0,
            propsForDots: {
              r: '4',
              strokeWidth: '2',
              stroke: '#fff',
            },
            propsForBackgroundLines: {
              stroke: '#ff638455', // Rose clair
              strokeDasharray: '4', // Tirets courts
              strokeWidth: 1,
            },
          }}
          bezier
          style={{ borderRadius: 8, marginVertical: 8 }}
          withVerticalLines={true}
          withHorizontalLines={true}
          withInnerLines={true}
        />
      )}
    </View>
  );
};

export default Logtimes;

const styles = StyleSheet.create({
  container: {
    margin: 20,
    padding: 10,
    borderRadius: 8,
    backgroundColor: '#fff',
  },
  title: {
    fontSize: 18,
    fontWeight: '700',
    color: '#d8005d',
    marginBottom: 8,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  arrow: {
    fontSize: 24,
    paddingHorizontal: 10,
    color: '#1188aa',
  },
  date: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
  },
});
