import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  timeout: 15000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      const publicPaths = [
        '/login',
        '/register',
        '/',
        '/home',
        '/shop',
        '/branches',
        '/custom-cake',
        '/faq',
        '/contact',
        '/track-order',
        '/about',
      ];
      const currentPath = window.location.pathname;
      const isPublic = publicPaths.some((p) => currentPath === p || currentPath.startsWith('/product/'));

      if (!isPublic && localStorage.getItem('token')) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login?expired=1';
      }
    }
    return Promise.reject(error);
  }
);

export default api;
