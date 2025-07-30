import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import JobsDone from '@/components/jobs/JobsDone';
import { ApiActions } from '@/services/ApiServices';

jest.mock('@/services/ApiServices', () => ({
  ApiActions: {
    get: jest.fn(),
  },
}));

const mockPromotions = [
  {
    promotion_id: 'promo1',
    promotion_name: 'Promo 2025',
  },
];

const mockUnits = [
  {
    unit_id: 'unit1',
    unit_name: 'Développement Mobile',
    promotion_id: 'promo1',
  },
];

const mockJobsDone = [
  {
    job_name: 'Projet React Native',
    registration_id: 'reg1',
    job_unit_id: 'unit1',
    job_unit_name: 'Développement Mobile',
  },
  {
    job_name: 'Projet Backend',
    registration_id: 'reg2',
    job_unit_id: 'unit2',
    job_unit_name: 'Serveur/API',
  },
];

describe('JobsDone', () => {
  beforeEach(() => {
    jest.clearAllMocks();

    (ApiActions.get as jest.Mock).mockImplementation(({ route }) => {
      if (route === 'promotion/history') {
        return Promise.resolve({ status: 200, data: mockPromotions });
      }
      if (route === 'promotion/unit') {
        return Promise.resolve({ status: 200, data: mockUnits });
      }
      if (route === 'job/done') {
        return Promise.resolve({ status: 200, data: mockJobsDone });
      }
      return Promise.resolve({ status: 200, data: [] });
    });
  });

  it('affiche les jobs finis après chargement', async () => {
    const { getByText } = render(<JobsDone />);

    await waitFor(() => {
      expect(getByText('Projet React Native')).toBeTruthy();
    });
  });

  it('filtre les jobs par unité via le picker', async () => {
    const { getByText, getByDisplayValue, queryByText } = render(<JobsDone />);

    await waitFor(() => {
      expect(getByText('Projet React Native')).toBeTruthy();
    });

    const unitPicker = getByDisplayValue('Toutes les unités');
    fireEvent(unitPicker, 'valueChange', 'unit1');

    await waitFor(() => {
      expect(getByText('Projet React Native')).toBeTruthy();
      expect(queryByText('Projet Backend')).toBeNull();
    });
  });

  it('ouvre la modale en cliquant sur un job', async () => {
    const { getByText, queryByText } = render(<JobsDone />);

    await waitFor(() => {
      expect(getByText('Projet React Native')).toBeTruthy();
    });

    fireEvent.press(getByText('Projet React Native'));

    await waitFor(() => {
      expect(queryByText(/Fermer/i)).toBeTruthy(); // bouton de la modale
    });
  });
});
