import { useContext } from 'react';
import AuthContext from '@/context/authContext';
import type { AuthContextType } from '@/types/authTypes';

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
