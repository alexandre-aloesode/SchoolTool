import React, { createContext, useState, useEffect, ReactNode } from 'react';
import { Session } from '@/utils/session';
import type { UserData, AuthContextType, UserSession } from '@/types/authTypes';
import { useRouter } from 'expo-router';

const AuthContext = createContext<AuthContextType | null>(null);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [user, setUser] = useState<UserData | null>(null);
  const [session, setSession] = useState<UserSession | null>(null);
  const router = useRouter();

  useEffect(() => {
    const loadStored = async () => {
      const storedUser = await Session.getUserData();
      const storedSession = await Session.getSession();
      if (storedUser) setUser(storedUser);
      if (storedSession) setSession(storedSession);
    };
    loadStored();
  }, []);

  const logout = async () => {
    try {
      await Session.clear();
      setUser(null);
      setSession(null);
      router.replace('/');
    } catch (error) {
      console.error('Erreur lors de la déconnexion :', error);
    }
  };

  return (
    <AuthContext.Provider
      value={{ user, session, setUser, setSession, logout }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
