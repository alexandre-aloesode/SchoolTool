import axios from "axios";
import config from "../config";
import AsyncStorage from "@react-native-async-storage/async-storage";

async function getSession() {
  try {
    const session = await AsyncStorage.getItem("userSession");
    return session ? JSON.parse(session) : null;
  } catch (error) {
    console.error("Error fetching session: ", error);
    return null;
  }
}

function buildUrl(params) {
  let url = "";
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
    const session = await getSession();
    let params = {
      method: "get",
      maxBodyLength: Infinity,
      url: `${config.LPTF_API_URL}/${route}?${url}`,
      headers: {
        Token: session?.accessToken || "",
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error("GET request error: ", error);
      throw error;
    }
  },

  async post(payload) {
    let route = payload.route;
    const body = payload.params;
    const session = await getSession();

    let params = {
      method: "post",
      maxBodyLength: Infinity,
      url: `${config.LPTF_API_URL}/${route}?`,
      data: new URLSearchParams(body).toString(),
      headers: {
        Token: session?.accessToken || "",
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error("POST request error: ", error);
      throw error;
    }
  },

  async put(payload) {
    let route = payload.route;
    const bodyParams = payload.params;
    const session = await getSession();

    let params = {
      method: "put",
      maxBodyLength: Infinity,
      url: `${config.LPTF_API_URL}/${route}?`,
      data: new URLSearchParams(bodyParams).toString(),
      headers: {
        Token: session?.accessToken || "",
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error("PUT request error: ", error);
      throw error;
    }
  },

  async delete(payload) {
    let route = payload.route;
    const bodyParams = payload.params;
    const session = await getSession();

    let params = {
      method: "delete",
      maxBodyLength: Infinity,
      url: `${config.LPTF_API_URL}/${route}?`,
      data: new URLSearchParams(bodyParams).toString(),
      headers: {
        Token: session?.accessToken || "",
      },
    };
    try {
      const response = await axios.request(params);
      return response;
    } catch (error) {
      console.error("DELETE request error: ", error);
      throw error;
    }
  },
};
