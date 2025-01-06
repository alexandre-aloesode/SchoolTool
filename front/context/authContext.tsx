import React, { createContext, useState, useEffect, ReactNode } from "react";
import * as SecureStore from "expo-secure-store";
import axios from "axios";

// Définir un type pour l'utilisateur
interface User {
  token: string;
}

// Définir un type pour le contexte
interface AuthContextType {
  user: User | null;
  login: (credentials: { email: string; password: string }) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);

  // Charger les informations utilisateur au lancement
  useEffect(() => {
    // const loadUser = async () => {
    //   const token = await SecureStore.getItemAsync("authToken");
    //   if (token) {
    //     setUser({ token }); // Décoder et charger plus de données si nécessaire
    //   }
    // };
    // loadUser();
  }, []);

  // Fonction de connexion
  const login = async (credentials: { email: string; password: string }) => {
    try {
      // Exemple d'appel API pour l'authentification
      const response = await axios.post("https://api.example.com/login", credentials);
      const token = response.data.token;
      
      // Stockage sécurisé du token
      await SecureStore.setItemAsync("authToken", token);
      setUser({ token });
    } catch (error) {
      console.error("Erreur de connexion", error);
    }
  };

  // Fonction de déconnexion
  const logout = async () => {
    await SecureStore.deleteItemAsync("authToken");
    setUser(null); // Réinitialiser l'état utilisateur
  };

  return (
    <AuthContext.Provider value={{ user, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
