import React, { createContext, useState, useEffect, ReactNode } from "react";
import * as SecureStore from "expo-secure-store";
import axios from "axios";
import AsyncStorage from "@react-native-async-storage/async-storage";

// Définir un type pour l'utilisateur
interface User {
  // email:string;
  // role:string;
  token: string;
  authtoken: string;
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
    const loadUser = async () => {
      const userData = await AsyncStorage.getItem("userData");
      if (userData) {
        const parsedData = JSON.parse(userData);
        setUser({ 
          token:parsedData.token,
          authtoken:parsedData.authtoken

        });
        console.log("userData", parsedData);
        
      }
    };
    loadUser();
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
    await AsyncStorage.removeItem("userData");
    setUser(null); // Réinitialiser l'état utilisateur
  };

  return (
    <AuthContext.Provider value={{ user, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
