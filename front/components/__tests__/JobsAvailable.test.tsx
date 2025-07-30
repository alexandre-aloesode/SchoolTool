import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import JobsAvailable from '../jobs/JobsAvailable';
import { ApiActions } from '@/services/ApiServices';

jest.mock('@/services/ApiServices', () => ({
  ApiActions: {
    get: jest.fn(),
  },
}));

const mockJobs = [
  {
    job_id: '1',
    job_name: 'Développeur Frontend',
    job_unit_name: 'Unit 1',
  },
  {
    job_id: '2',
    job_name: 'Développeur Backend',
    job_unit_name: 'Unit 2',
  },
];

describe('JobsAvailable', () => {
  beforeEach(() => {
    (ApiActions.get as jest.Mock).mockResolvedValue({
      status: 200,
      data: mockJobs,
    });
  });

  it('affiche les jobs disponibles après chargement', async () => {
    const { getByText } = render(<JobsAvailable />);

    await waitFor(() => {
      expect(getByText('Développeur Frontend')).toBeTruthy();
      expect(getByText('Développeur Backend')).toBeTruthy();
    });
  });

  it('filtre les jobs par unité via le picker', async () => {
    const { getByText, getByDisplayValue, queryByText } = render(
      <JobsAvailable />,
    );

    await waitFor(() => {
      expect(getByText('Développeur Frontend')).toBeTruthy();
    });

    const picker = getByDisplayValue('Toutes les Units');
    fireEvent(picker, 'valueChange', 'Unit 1');

    await waitFor(() => {
      expect(getByText('Développeur Frontend')).toBeTruthy();
      expect(queryByText('Développeur Backend')).toBeNull();
    });
  });

  it('ouvre la modale en cliquant sur un job', async () => {
    const { getByText, queryByText } = render(<JobsAvailable />);

    await waitFor(() => {
      expect(getByText('Développeur Frontend')).toBeTruthy();
    });

    fireEvent.press(getByText('Développeur Frontend'));

    await waitFor(() => {
      expect(queryByText(/Groupe/i)).toBeTruthy();
    });
  });
});
