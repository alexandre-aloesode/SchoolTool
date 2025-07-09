import AsyncStorage from '@react-native-async-storage/async-storage';
import { UserSession, UserData } from '@/types/authTypes';

const SESSION_KEY = 'userSession';
const USERDATA_KEY = 'userData';

export const Session = {
  async set(session: UserSession, userData: UserData) {
    await AsyncStorage.setItem(SESSION_KEY, JSON.stringify(session));
    await AsyncStorage.setItem(USERDATA_KEY, JSON.stringify(userData));
  },

  async getSession(): Promise<UserSession | null> {
    const raw = await AsyncStorage.getItem(SESSION_KEY);
    return raw ? JSON.parse(raw) : null;
  },

  async getUserData(): Promise<UserData | null> {
    const raw = await AsyncStorage.getItem(USERDATA_KEY);
    return raw ? JSON.parse(raw) : null;
  },

  async clear() {
    await AsyncStorage.multiRemove([SESSION_KEY, USERDATA_KEY]);
  },

  async updateAccessToken(newToken: string) {
    const session = await Session.getSession();
    if (!session) return;
    const updated = { ...session, accessToken: newToken };
    await AsyncStorage.setItem(SESSION_KEY, JSON.stringify(updated));
  },

  async updateSessionOnly(newSession: UserSession) {
    await AsyncStorage.setItem('userSession', JSON.stringify(newSession));
  },
};
