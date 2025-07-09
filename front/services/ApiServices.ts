import axios from 'axios';
import { ENV } from '@/utils/env';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { router } from 'expo-router';
import { Session } from '@/utils/session';
import type { AxiosResponse } from 'axios';
import type { UserSession } from '@/types/authTypes';

type ApiPayload = {
  route: string;
  params: Record<string, any>;
};

async function getApiToken() {
  try {
    const session = await Session.getSession();

    if (!session) {
      router.push('/');
      return null;
    }

    if (isTokenExpired(session.accessToken)) {
      const newToken = await refreshToken(session);
      if (newToken) {
        await Session.updateAccessToken(newToken);
        return newToken;
      } else {
        await Session.clear();
        router.push('/');
        return null;
      }
    }

    return session.accessToken;
  } catch (error) {
    console.error('Error fetching session: ', error);
    return null;
  }
}

function isTokenExpired(token: string): boolean {
  if (!token) return true;
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const payload = JSON.parse(atob(base64));

    const currentTime = Math.floor(Date.now() / 1000);

    return payload.exp < currentTime;
  } catch (err) {
    console.error('Failed to decode token:', err);
    return true;
  }
}

async function refreshToken(session: UserSession): Promise<string | null> {
  try {
    const formData = new FormData();
    formData.append('authtoken', session?.authToken);
    let params = {
      method: 'post',
      maxBodyLength: Infinity,
      url: `${ENV.LPTF_AUTH_API_URL}/refresh`,
      data: formData,
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    };
    const response = await axios.request(params);
    const refreshedToken = response.data?.token;

    // if (!refreshedToken) {
    //   console.error('No token received from refresh');
    //   auth.logout();
    //   return null;
    // }

    return refreshedToken;
  } catch (error) {
    console.error('Failed to refresh token:', error);
    await AsyncStorage.removeItem('userSession'); // optional: force logout
    throw error;
  }
}

function buildUrl(params: Record<string, string | number | (string | number)[]>): string {
  let url = '';
  Object.keys(params).forEach((key) => {
    if (Array.isArray(params[key])) {
      params[key].forEach((element) => {
        url += `${key}[]=${element}&`;
      });
    } else {
      url += `${key}=${params[key]}&`;
    }
  });
  return url;
}

export const ApiActions = {
  
  async get(payload: ApiPayload): Promise<AxiosResponse | null> {
    let route = payload.route;
    let url = buildUrl(payload.params);

    const token = await getApiToken();
    if (!token) {
      router.push('/');
      return null;
    }

    let params = {
      method: 'get',
      maxBodyLength: Infinity,
      url: `${ENV.LPTF_API_URL}/${route}?${url}`,
      headers: {
        Token: token || '',
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error('GET request error: ', error);
      throw error;
    }
  },

  async post(payload: ApiPayload): Promise<AxiosResponse | null> {
    let route = payload.route;
    const body = payload.params;

    const token = await getApiToken();
    if (!token) {
      router.push('/');
      return null;
    }

    let params = {
      method: 'post',
      maxBodyLength: Infinity,
      url: `${ENV.LPTF_API_URL}/${route}?`,
      data: new URLSearchParams(body).toString(),
      headers: {
        Token: token || '',
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error('POST request error: ', error);
      throw error;
    }
  },

  async put(payload: ApiPayload): Promise<AxiosResponse | null> {
    let route = payload.route;
    const bodyParams = payload.params;

    const token = await getApiToken();
    if (!token) {
      router.push('/');
      return null;
    }

    let params = {
      method: 'put',
      maxBodyLength: Infinity,
      url: `${ENV.LPTF_API_URL}/${route}?`,
      data: new URLSearchParams(bodyParams).toString(),
      headers: {
        Token: token || '',
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error('PUT request error: ', error);
      throw error;
    }
  },

  async delete(payload: ApiPayload): Promise<AxiosResponse | null> {
    let route = payload.route;
    const bodyParams = payload.params;

    const token = await getApiToken();
    if (!token) {
      router.push('/');
      return null;
    }

    let params = {
      method: 'delete',
      maxBodyLength: Infinity,
      url: `${ENV.LPTF_API_URL}/${route}?`,
      data: new URLSearchParams(bodyParams).toString(),
      headers: {
        Token: token || '',
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error('DELETE request error: ', error);
      throw error;
    }
  },
};
