import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import Logtimes from '../dashboard/logtimes';
import { ApiActions } from '@/services/ApiServices';
import dayjs from 'dayjs';

jest.mock('@/services/ApiServices', () => ({
  ApiActions: {
    get: jest.fn(),
  },
}));

const mockLogtimes = [
  {
    logtime_day: dayjs().startOf('week').add(1, 'day').format('YYYY-MM-DD'),
    logtime_algo2: 180, // 3h
  },
  {
    logtime_day: dayjs().startOf('week').add(3, 'day').format('YYYY-MM-DD'),
    logtime_algo2: 240, // 4h
  },
];

describe('Logtimes', () => {
  beforeEach(() => {
    jest.clearAllMocks();

    (ApiActions.get as jest.Mock).mockResolvedValue({
      status: 200,
      data: mockLogtimes,
    });
  });

  it('affiche le graphique avec les temps de présence', async () => {
    const { getByText } = render(<Logtimes />);

    await waitFor(() => {
      expect(getByText(/Semaine du/)).toBeTruthy();
      expect(getByText(/Total des heures : 7h/)).toBeTruthy(); // 3h + 4h = 7h
    });
  });

  it('change de semaine avec les flèches de navigation', async () => {
    const { getByText } = render(<Logtimes />);

    const previousWeek = getByText('◀');
    const nextWeek = getByText('▶');

    fireEvent.press(previousWeek);
    fireEvent.press(nextWeek);

    await waitFor(() => {
      expect(getByText(/Semaine du/)).toBeTruthy();
    });
  });

  it('affiche un loader pendant le chargement', async () => {
    const { getByTestId } = render(<Logtimes />);
    expect(getByTestId('ActivityIndicator')).toBeTruthy();
  });
});
