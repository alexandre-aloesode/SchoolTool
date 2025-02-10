import React, { createContext, useState, useEffect, ReactNode } from "react";
import * as SecureStore from "expo-secure-store";
import * as AuthSession from "expo-auth-session";
import axios from "axios";
import { jwtDecode } from "jwt-decode";
import { Alert, Platform } from "react-native";
import config from "../config.js";

// Définition du type utilisateur
interface User {
  id: string;
  email: string;
  role: string;
  token: string;
}

// Définition du type pour le contexte
interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (credentials: { email: string; password: string }) => Promise<void>;
  loginWithGoogle: () => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  const [request, response, promptAsync] = AuthSession.useAuthRequest(
    {
      clientId: Platform.select({
        ios: config.IOS_CLIENT_ID,
        android: config.ANDROID_CLIENT_ID,
        default: config.LPTF_GOOGLE_CLIENT_ID,
      }),
      redirectUri: AuthSession.makeRedirectUri({
        scheme: "com.schooltool.authsessiongoogle",
      }),
      usePKCE: true,
      scopes: ["openid", "profile", "email"],
    },
    { authorizationEndpoint: "https://accounts.google.com/o/oauth2/v2/auth" }
  );

  useEffect(() => {
    const loadUser = async () => {
      try {
        const token = await SecureStore.getItemAsync("authToken");
        if (token) {
          fetchUser(token);
        } else {
          setLoading(false);
        }
      } catch (error) {
        console.error("Erreur lors du chargement des données utilisateur", error);
        setLoading(false);
      }
    };
    loadUser();
  }, []);

  useEffect(() => {
    const handleGoogleResponse = async () => {
      if (response?.type === "success" && response.params?.code) {
        try {
          const tokenResponse = await fetch("https://oauth2.googleapis.com/token", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              code: response.params.code,
              client_secret: config.GOOGLE_CLIENT_SECRET,
              client_id: Platform.select({
                ios: config.IOS_CLIENT_ID,
                android: config.ANDROID_CLIENT_ID,
                default: config.LPTF_GOOGLE_CLIENT_ID,
              }),
              redirect_uri: AuthSession.makeRedirectUri({
                scheme: "com.schooltool.authsessiongoogle",
              }),
              grant_type: "authorization_code",
            }),
          });

          const tokenData = await tokenResponse.json();
          if (tokenData.access_token) {
            console.log("tokenData", tokenData);
            
            await fetchUser(tokenData.id_token);
          } else {
            Alert.alert("Erreur", "Impossible d'obtenir un jeton d'accès");
          }
        } catch (error) {
          Alert.alert("Erreur", "Problème lors de la connexion avec Google");
        }
      }
    };
    console.log("response", response);
    
    handleGoogleResponse();
  }, [response]);

  const fetchUser = async (token: string) => {
    try {
      // const decodedToken = jwtDecode(token);
      // await SecureStore.setItemAsync("userData", JSON.stringify(decodedToken));
      // console.log("decodedToken", decodedToken);

      // setUser({
      //   id: decodedToken.id,
      //   email: decodedToken.email,
      //   role: decodedToken.role,
      //   token,
      // });

      var base64Url = token.split(".")[1];
      var base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
      var payload = decodeURIComponent(
        atob(base64)
          .split("")
          .map(function (c) {
            return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
          })
          .join("")
      );
      setLoading(false);

      return JSON.parse(payload);
    } catch (error) {
      console.error("Erreur lors du décodage du token", error);
    }
    setLoading(false);
  };

  const login = async (credentials: { email: string; password: string }) => {
    try {
      const response = await axios.post("https://api.example.com/login", credentials);
      const token = response.data.token;
      await SecureStore.setItemAsync("authToken", token);
      fetchUser(token);
    } catch (error) {
      Alert.alert("Erreur", "Impossible de se connecter");
    }
  };

  const loginWithGoogle = async () => {
    try {
      await promptAsync();
    } catch (error) {
      Alert.alert("Erreur", "Problème lors de la connexion avec Google");
    }
  };

  const logout = async () => {
    try {
      await SecureStore.deleteItemAsync("authToken");
      await SecureStore.deleteItemAsync("userData");
      setUser(null);
    } catch (error) {
      Alert.alert("Erreur", "Impossible de se déconnecter");
    }
  };

  return <AuthContext.Provider value={{ user, loading, login, loginWithGoogle, logout }}>{children}</AuthContext.Provider>;
};

export default AuthContext;
