import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  TouchableOpacity,
  Dimensions,
} from 'react-native';
import { BarChart } from 'react-native-chart-kit';
import { ApiActions } from '@/services/ApiServices';
import dayjs from 'dayjs';
import type { Logtime } from '@/types/logtimesTypes';

const screenWidth = Dimensions.get('window').width;
const chartPadding = 20;

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

    if (logtimesResponse?.status === 200) {
      setLogtimes(logtimesResponse.data || []);
    }

    setLoading(false);
  };

  useEffect(() => {
    fetchLogtimes();
  }, [weekStart]);

  const getChartData = () => {
    const labels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    const data = [];
    let totalMinutes = 0;

    for (let i = 0; i < 7; i++) {
      const date = weekStart.add(i, 'day');
      const log = logtimes.find((l) =>
        dayjs(l.logtime_day).isSame(date, 'day'),
      );
      const minutes = log ? log.logtime_algo2 : 0;
      totalMinutes += minutes;
      data.push((minutes / 60).toFixed(1));
    }

    return {
      labels,
      datasets: [{ data: data.map(Number) }],
      total: totalMinutes,
    };
  };

  const chartData = getChartData();

  return (
    <View style={styles.cardContainer}>
      <Text style={styles.sectionTitle}>Temps de présence</Text>
      <View style={styles.header}>
        <TouchableOpacity
          onPress={() => setWeekStart((prev) => prev.subtract(7, 'day'))}
        >
          <Text style={styles.arrow}>◀</Text>
        </TouchableOpacity>

        <View style={{ alignItems: 'center' }}>
          <Text style={styles.week}>
            Semaine du {weekStart.format('DD/MM/YYYY')}
          </Text>
          <Text style={styles.total}>
            Total des heures : {Math.floor(chartData.total / 60)}h
          </Text>
        </View>

        <TouchableOpacity
          onPress={() => setWeekStart((prev) => prev.add(7, 'day'))}
        >
          <Text style={styles.arrow}>▶</Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#3B82F6" />
      ) : (
        <BarChart
          data={{
            labels: chartData.labels,
            datasets: chartData.datasets,
          }}
          width={Dimensions.get('window').width * 0.84}
          height={220}
          fromZero
          yAxisLabel=""
          yAxisSuffix="h"
          withInnerLines={false}
          withHorizontalLabels
          withVerticalLabels
          chartConfig={{
            backgroundGradientFrom: '#fff',
            backgroundGradientTo: '#fff',
            decimalPlaces: 1,
            color: () => '#3B82F6',
            labelColor: () => '#111',
            fillShadowGradient: '#3B82F6',
            fillShadowGradientOpacity: 1,
            barPercentage: 0.5,
          }}
          style={{
            borderRadius: 10,
            alignSelf: 'center',
          }}
        />
      )}
    </View>
  );
};

export default Logtimes;

const styles = StyleSheet.create({
  cardContainer: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 12,
    paddingVertical: 14,
    paddingHorizontal: 10,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    alignSelf: 'stretch',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  arrow: {
    fontSize: 20,
    color: '#3B82F6',
    paddingHorizontal: 12,
  },
  week: {
    fontWeight: 'bold',
    fontSize: 16,
    color: '#111',
  },
  total: {
    fontSize: 13,
    color: '#333',
    marginTop: 2,
  },
  sectionTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#0084FA',
    textAlign: 'center',
    marginBottom: 10,
  },
});
