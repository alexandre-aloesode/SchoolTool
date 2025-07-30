import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import JobsInProgress from '@/components/jobs/JobsInProgress';
import { ApiActions } from '@/services/ApiServices';

jest.mock('@/services/ApiServices', () => ({
  ApiActions: {
    get: jest.fn(),
  },
}));

const mockJobs = [
  {
    job_id: '1',
    job_name: 'Projet 1',
    start_date: '2024-01-01',
    end_date: '2099-12-31',
    registration_id: 'reg-1',
    job_is_done: false,
  },
  {
    job_id: '2',
    job_name: 'Projet en Retard',
    start_date: '2023-01-01',
    end_date: '2023-06-01',
    registration_id: 'reg-2',
    job_is_done: false,
  },
];

describe('JobsInProgress', () => {
  beforeEach(() => {
    jest.clearAllMocks();

    (ApiActions.get as jest.Mock).mockImplementation(({ route }) => {
      if (
        route === '/job/await' ||
        route === '/job/progress' ||
        route === '/job/ready'
      ) {
        return Promise.resolve({
          status: 200,
          data: route === '/job/ready' ? [mockJobs[1]] : [mockJobs[0]],
        });
      }

      return Promise.resolve({ status: 200, data: [] });
    });
  });

  it('affiche les projets en cours et en retard', async () => {
    const { getByText } = render(<JobsInProgress />);

    await waitFor(() => {
      expect(getByText('Projet 1')).toBeTruthy();
      expect(getByText('Projet en Retard')).toBeTruthy();
    });

    expect(getByText(/Retard de/)).toBeTruthy(); // Vérifie qu'une alerte de retard est visible
  });

  it('ouvre la modale en cliquant sur un projet', async () => {
    const { getByText, queryByText } = render(<JobsInProgress />);

    await waitFor(() => {
      expect(getByText('Projet 1')).toBeTruthy();
    });

    fireEvent.press(getByText('Projet 1'));

    await waitFor(() => {
      expect(queryByText(/Fermer/i)).toBeTruthy(); // Bouton dans la modale
    });
  });
});
