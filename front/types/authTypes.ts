export interface UserSession {
    accessToken: string;
    authToken: string;
    googleAccessToken: string;
    googleExpiresIn: number;
    googleExpiresAt?: number;
    googleRefreshToken: string;
    googleScope: string;
  }

  export interface UserData {
    id: string;
    email: string;
    role: string;
    scope: string[];
  }
  
  
  export interface AuthContextType {
    user: UserData | null;
    session: UserSession | null;
    setUser: React.Dispatch<React.SetStateAction<UserData | null>>;
    setSession: React.Dispatch<React.SetStateAction<UserSession | null>>;
    logout: () => Promise<void>;
  }
  