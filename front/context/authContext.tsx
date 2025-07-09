import React, { createContext, useState, useEffect, ReactNode } from 'react';
import { Session } from '@/utils/session';
import type { UserData, AuthContextType, UserSession } from '@/types/authTypes';

const AuthContext = createContext<AuthContextType | null>(null);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [user, setUser] = useState<UserData | null>(null);
  const [session, setSession] = useState<UserSession | null>(null);

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
    await Session.clear();
    setUser(null);
    setSession(null);
  };

  return (
    <AuthContext.Provider value={{ user, session, setUser, setSession, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
