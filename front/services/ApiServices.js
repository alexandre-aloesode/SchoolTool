import axios from 'axios';
import { ENV } from '@/utils/env';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { router } from 'expo-router';
import React, { useContext } from 'react';
import AuthContext from '@/context/authContext';

const auth = useContext(AuthContext);

async function getApiToken() {
  try {
    const session = await AsyncStorage.getItem('userSession');

    if (session === undefined || session === null) {
      router.push('/');
      return null;
    }

    const parsedSession = JSON.parse(session);

    if (isTokenExpired(parsedSession.accessToken)) {
      const newToken = await refreshToken(parsedSession);
      if (newToken) {
        await AsyncStorage.setItem(
          'userSession',
          JSON.stringify({
            ...parsedSession,
            accessToken: newToken,
          }),
        );
        return newToken;
      } else {
        router.push('/');
        return null;
      }
    }

    return parsedSession.accessToken;
  } catch (error) {
    console.error('Error fetching session: ', error);
    return null;
  }
}

function isTokenExpired(token) {
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

async function refreshToken(session) {
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
    console.log('Response from refresh token:', response.data);

    const refreshedToken = response.data?.token;

    if (!refreshedToken) {
      console.error('No token received from refresh');
      auth.logout();
      return null;
    }

    return refreshedToken;
  } catch (error) {
    console.error('Failed to refresh token:', error);
    await AsyncStorage.removeItem('userSession'); // optional: force logout
    throw error;
  }
}

function buildUrl(params) {
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
  async get(payload) {
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

  async post(payload) {
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

  async put(payload) {
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

  async delete(payload) {
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
