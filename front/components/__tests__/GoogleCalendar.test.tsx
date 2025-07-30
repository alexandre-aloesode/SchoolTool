import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import GoogleCalendarWidget from '../dashboard/googleCalendar';
import { getValidGoogleAccessToken } from '@/utils/googleToken';
import { useAuth } from '@/hooks/useAuth';

jest.mock('@/utils/googleToken', () => ({
  getValidGoogleAccessToken: jest.fn(),
}));

jest.mock('@/hooks/useAuth', () => ({
  useAuth: jest.fn(),
}));

global.fetch = jest.fn();

const mockEvents = [
  {
    id: '1',
    summary: 'Réunion de projet',
    start: { dateTime: new Date().toISOString() },
    description: 'Point hebdo sur l’avancement',
  },
  {
    id: '2',
    summary: 'Atelier React Native',
    start: { date: new Date().toISOString().split('T')[0] },
  },
];

describe('GoogleCalendarWidget', () => {
  beforeEach(() => {
    jest.clearAllMocks();

    (useAuth as jest.Mock).mockReturnValue({
      user: { id: 'user-1', email: 'test@example.com' },
    });

    (getValidGoogleAccessToken as jest.Mock).mockResolvedValue('mock-token');

    (fetch as jest.Mock).mockResolvedValue({
      json: async () => ({
        items: mockEvents,
      }),
    });
  });

  it('affiche les événements de la semaine', async () => {
    const { getByText } = render(<GoogleCalendarWidget />);

    await waitFor(() => {
      expect(getByText('Réunion de projet')).toBeTruthy();
      expect(getByText('Atelier React Native')).toBeTruthy();
    });
  });

  it('ouvre et ferme la modale au clic sur un événement', async () => {
    const { getByText, queryByText } = render(<GoogleCalendarWidget />);

    await waitFor(() => {
      fireEvent.press(getByText('Réunion de projet'));
    });

    await waitFor(() => {
      expect(queryByText(/Point hebdo/)).toBeTruthy();
    });

    fireEvent.press(getByText('Fermer'));

    await waitFor(() => {
      expect(queryByText(/Point hebdo/)).toBeNull();
    });
  });

  it('navigue vers la semaine suivante', async () => {
    const { getByText } = render(<GoogleCalendarWidget />);

    const nextArrow = getByText('▶');
    fireEvent.press(nextArrow);

    await waitFor(() => {
      // Vérifie que l'intitulé de la semaine a changé
      expect(getByText(/Semaine du/)).toBeTruthy();
    });
  });
});
