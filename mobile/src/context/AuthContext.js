import AsyncStorage from '@react-native-async-storage/async-storage';
import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { api } from '../services/api';
import { SESSION_KEYS } from '../services/draftStorage';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [token, setToken] = useState(null);
  const [user, setUser] = useState(null);
  const [hasAccountOnDevice, setHasAccountOnDevice] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function restoreSession() {
      const [[, storedToken], [, storedUser], [, storedAccountMarker]] = await AsyncStorage.multiGet([
        SESSION_KEYS.token,
        SESSION_KEYS.user,
        SESSION_KEYS.hasAccountOnDevice,
      ]);

      setHasAccountOnDevice(storedAccountMarker === 'true' || Boolean(storedToken));
      if (storedToken) {
        await AsyncStorage.setItem(SESSION_KEYS.hasAccountOnDevice, 'true');
        try {
          const session = await api.me(storedToken);
          setToken(storedToken);
          setUser(session.user);
        } catch (error) {
          if (error.status === 401) {
            await AsyncStorage.multiRemove([SESSION_KEYS.token, SESSION_KEYS.user]);
          } else {
            setToken(storedToken);
            setUser(storedUser ? JSON.parse(storedUser) : null);
          }
        }
      }
      setLoading(false);
    }

    restoreSession();
  }, []);

  async function persistSession(response) {
    setToken(response.token);
    setUser(response.user);
    setHasAccountOnDevice(true);
    await AsyncStorage.multiSet([
      [SESSION_KEYS.token, response.token],
      [SESSION_KEYS.user, JSON.stringify(response.user)],
      [SESSION_KEYS.hasAccountOnDevice, 'true'],
    ]);
  }

  async function googleLogin(exchangeCode) {
    const response = await api.googleExchange(exchangeCode);
    await persistSession(response);
    return response;
  }

  async function logout() {
    try {
      if (token) {
        const pushToken = await AsyncStorage.getItem(SESSION_KEYS.pushToken);
        if (pushToken) {
          try {
            await api.unregisterPushToken(pushToken, token);
          } catch (_) {
            // Logout must still succeed if the device is temporarily offline.
          }
        }
        await api.logout(token);
      }
    } finally {
      setToken(null);
      setUser(null);
      await AsyncStorage.multiRemove([SESSION_KEYS.token, SESSION_KEYS.user, SESSION_KEYS.pushToken]);
    }
  }

  // After the account is deleted on the server: its tokens are gone, so only the device is cleared.
  async function forgetSession() {
    setToken(null);
    setUser(null);
    setHasAccountOnDevice(false);
    await AsyncStorage.multiRemove([SESSION_KEYS.token, SESSION_KEYS.user, SESSION_KEYS.pushToken, SESSION_KEYS.hasAccountOnDevice]);
  }

  async function expireSession() {
    setToken(null);
    setUser(null);
    setHasAccountOnDevice(true);
    await AsyncStorage.multiSet([[SESSION_KEYS.hasAccountOnDevice, 'true']]);
    await AsyncStorage.multiRemove([SESSION_KEYS.token, SESSION_KEYS.user]);
  }

  const value = useMemo(
    () => ({
      token,
      user,
      loading,
      hasAccountOnDevice,
      isAuthenticated: Boolean(token),
      googleLogin,
      logout,
      forgetSession,
      expireSession,
    }),
    [token, user, loading, hasAccountOnDevice],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  return useContext(AuthContext);
}
