export interface User {
    accessToken: string;
    authToken: string;
    googleAccessToken: string;
    googleExpiresAt: number;
    googleExpiresIn: number;
    googleRefreshToken: string;
    googleScope: string;
  }
  
  
  export interface AuthContextType {
    user: User | null;
    setUser: React.Dispatch<React.SetStateAction<User | null>>;
    logout: () => Promise<void>;
  }
  