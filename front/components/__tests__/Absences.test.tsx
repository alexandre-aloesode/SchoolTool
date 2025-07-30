import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import UploadAbsences from '../absences/AbsencesView';
import { ApiActions } from '@/services/ApiServices';

jest.mock('@/services/ApiServices', () => ({
  ApiActions: {
    get: jest.fn(),
  },
}));

const mockAbsences = [
  {
    absence_start_date: '2024-06-01',
    absence_end_date: '2024-06-03',
    absence_duration: 3,
    absence_status: 1, // Validée
  },
  {
    absence_start_date: '2024-07-01',
    absence_end_date: '2024-07-01',
    absence_duration: 1,
    absence_status: 0, // En attente
  },
];

describe('UploadAbsences', () => {
  beforeEach(() => {
    jest.clearAllMocks();

    (ApiActions.get as jest.Mock).mockResolvedValue({
      status: 200,
      data: mockAbsences,
    });
  });

  it('affiche les absences précédemment enregistrées', async () => {
    const { getByText } = render(<UploadAbsences />);

    await waitFor(() => {
      expect(getByText('Absences précédentes')).toBeTruthy();
      expect(getByText('01/06/2024 - 03/06/2024')).toBeTruthy();
      expect(getByText('3 jours')).toBeTruthy();
      expect(getByText('Validée')).toBeTruthy();

      expect(getByText('01/07/2024 - 01/07/2024')).toBeTruthy();
      expect(getByText('1 jour')).toBeTruthy();
      expect(getByText('En attente')).toBeTruthy();
    });
  });

  it('ouvre la modale en appuyant sur "+ Nouvelle absence"', async () => {
    const { getByText } = render(<UploadAbsences />);
    const addButton = getByText('+ Nouvelle absence');

    fireEvent.press(addButton);

    await waitFor(() => {
      // On attend que la modale apparaisse en conséquence
      expect(getByText(/Nouvelle absence/i)).toBeTruthy();
    });
  });
});
