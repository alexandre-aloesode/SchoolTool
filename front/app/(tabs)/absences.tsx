import React from 'react';
import { SafeAreaView, StyleSheet, View } from 'react-native';
import UploadAbsences from '@/components/absences/AbsencesView';
import Header from '@/components/global/Header';

export default function AbsencesMain() {
  return (
    <SafeAreaView style={styles.safeArea}>
      <Header />
      <View style={styles.container}>
        <UploadAbsences />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#f7f7f7',
  },
  container: {
    flex: 1,
    padding: 16,
    backgroundColor: '#fff',
  },
});
