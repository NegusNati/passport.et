# API Auth Flow – Frontend Integration Guide

This guide explains how to wire the React SPA to the Laravel API v1 authentication stack using @tanstack/react-query and TanStack Router. It assumes the API is deployed under `/api/v1` and that personal access tokens (PATs) are issued via Laravel Sanctum.

## Endpoint Surface
- `POST /api/v1/auth/register` – Create a new account (first/last name, phone number, email, password) and auto-issue a PAT.
- `POST /api/v1/auth/login` – Issue a new PAT after validating phone number/password credentials. Anonymous clients only.
- `GET /api/v1/auth/me` – Return the authenticated user profile (requires `Authorization: Bearer <token>`).
- `POST /api/v1/auth/logout` – Revoke the caller's current PAT (requires bearer token).
- All routes run behind the `throttle:api.v1.default` limiter:
  - 60 requests/minute for anonymous callers.
  - 120 requests/minute for authenticated users without a premium subscription.
  - 240 requests/minute for premium subscribers.
  - Throttled requests return `429` with `{ "status": "error", "code": "rate_limit_exceeded", "message": "Too many requests..." }`.

## Request & Response Reference

### POST /api/v1/auth/register
Headers
- `Accept: application/json`
- `Content-Type: application/json`

Body
```json
{
  "first_name": "Abebe",
  "last_name": "Bekele",
  "phone_number": "0911223344",
  "email": "abebe@example.com",
  "password": "password123"
}
```

Success `201 Created`
```json
{
  "token_type": "Bearer",
  "token": "1|990c1d...",
  "user": {
    "id": 42,
    "first_name": "Abebe",
    "last_name": "Bekele",
    "phone_number": "0911223344",
    "email": "abebe@example.com",
    "email_verified_at": null,
    "plan_type": null,
    "created_at": "2025-10-01T12:00:00Z",
    "updated_at": "2025-10-01T12:00:00Z"
  }
}
```

Notes
- Phone number must be 10 digits starting with `09` or `07`; email must be unique and lowercase.
- Passwords require at least 8 characters; enforce matching client-side policy.
- The API emits the `Registered` event so email verification emails continue to send if enabled.
- Response mirrors login payload; store the token the same way.

### POST /api/v1/auth/login
Headers
- `Accept: application/json`
- `Content-Type: application/json`

Body
```json
{
  "phone_number": "0911223344",
  "password": "secret",
  "device_name": "edge-chrome" // optional, stored for auditing
}
```

Success `200 OK`
```json
{
  "token_type": "Bearer",
  "token": "1|990c1d...",     // Sanctum plain-text token
  "user": {
    "id": 42,
    "first_name": "Abebe",
    "last_name": "Bekele",
    "phone_number": "0911000000",
    "email_verified_at": "2025-09-24T10:00:00Z",
    "plan_type": "premium",    // null if no subscription
    "created_at": "2024-04-12T08:21:54Z",
    "updated_at": "2025-09-20T13:14:02Z"
  }
}
```

Error semantics
- `422` with `{ "status": "error", "code": "invalid_credentials", "message": ... }` when the credentials fail (invalid phone number and/or password).
- Standard validation errors follow Laravel's JSON validation format if required fields are missing (e.g., phone number must be digits and start with 09 or 07).
- `429` throttle response as outlined above.

### GET /api/v1/auth/me
Headers
- `Accept: application/json`
- `Authorization: Bearer <token>`

Success `200 OK`
```json
{
  "data": {
    "id": 42,
    "first_name": "Abebe",
    "last_name": "Bekele",
    "phone_number": "0911000000",
    "email_verified_at": "2025-09-24T10:00:00Z",
    "plan_type": "premium",
    "created_at": "2024-04-12T08:21:54Z",
    "updated_at": "2025-09-20T13:14:02Z"
  }
}
```

Errors
- `401` if the token is missing/expired/revoked.
- `429` throttle payload.

### POST /api/v1/auth/logout
Headers
- `Accept: application/json`
- `Authorization: Bearer <token>`

Success `204 No Content`
- The user's current token is revoked server-side.

Errors
- `401` if no valid token.
- `429` throttle payload.

## Frontend Implementation Patterns

### 1. API Client & Query Setup
Create a shared HTTP client that automatically attaches the bearer token and surfaces rate-limit data. Use session storage or an encrypted, HttpOnly cookie proxy for persistence; avoid long-lived localStorage copies of the plain-text token.

```ts
// src/api/client.ts
import axios from 'axios';

let authToken: string | null = null;

export const api = axios.create({
  baseURL: '/api/v1',
  headers: { Accept: 'application/json' },
  withCredentials: false,
});

export function setAuthToken(token: string | null) {
  authToken = token;
}

api.interceptors.request.use((config) => {
  if (authToken) {
    config.headers = config.headers ?? {};
    config.headers.Authorization = `Bearer ${authToken}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 429) {
      const retryAfter = error.response.headers['retry-after'];
      error.retryAfterSeconds = Number(retryAfter ?? 0);
    }
    if (error.response?.status === 401) {
      // trigger a router redirect or auth reset downstream
    }
    return Promise.reject(error);
  }
);
```

Wire @tanstack/react-query with sensible defaults for stale times and retry handling:

```ts
// src/api/queryClient.ts
import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,
      retry: (failureCount, error: any) => {
        if (error?.response?.status === 401 || error?.response?.status === 422) {
          return false;
        }
        if (error?.response?.status === 429) {
          return failureCount < 1; // single retry after backoff
        }
        return failureCount < 2;
      },
    },
    mutations: {
      retry: 0,
    },
  },
});
```

### 2. Auth Queries & Mutations
Define helpers that encapsulate the login/me/logout flows.

```ts
// src/features/auth/api.ts
import { api, setAuthToken } from '@/api/client';
import { queryClient } from '@/api/queryClient';

interface LoginPayload {
  phone_number: string;  // 10 digits, starts with 09 or 07
  password: string;
  device_name?: string;
}

interface LoginResponse {
  token_type: 'Bearer';
  token: string;
  user: User;
}

export async function register(payload: RegisterPayload) {
  const { data } = await api.post<LoginResponse>('/auth/register', payload);
  setAuthToken(data.token);
  sessionStorage.setItem('auth-token', data.token);
  queryClient.setQueryData(['auth', 'user'], data.user);
  return data.user;
}

export async function login(payload: LoginPayload) {
  const { data } = await api.post<LoginResponse>('/auth/login', payload);
  setAuthToken(data.token);
  sessionStorage.setItem('auth-token', data.token);
  queryClient.setQueryData(['auth', 'user'], data.user);
  return data.user;
}

export async function fetchMe() {
  const { data } = await api.get<{ data: User }>('/auth/me');
  return data.data;
}

export async function logout() {
  await api.post('/auth/logout');
  setAuthToken(null);
  sessionStorage.removeItem('auth-token');
  queryClient.removeQueries({ queryKey: ['auth'] });
}
```

Hook them into React components using @tanstack/react-query:

```ts
// src/features/auth/hooks.ts
import { useMutation, useQuery } from '@tanstack/react-query';
import { fetchMe, login, logout, register } from './api';

export function useAuthUser(enabled = true) {
  return useQuery({
    queryKey: ['auth', 'user'],
    queryFn: fetchMe,
    enabled,
  });
}

export function useRegister() {
  return useMutation({
    mutationFn: register,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
}

export function useLogin() {
  return useMutation({
    mutationFn: login,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
}

export function useLogout() {
  return useMutation({
    mutationFn: logout,
  });
}
```

### 3. Session Bootstrap on App Load
Restore any persisted token before rendering the router so that initial route loaders can call `/auth/me` without hitting a 401 flash.

```ts
// src/main.tsx
const bootstrapAuth = () => {
  const persisted = sessionStorage.getItem('auth-token');
  if (persisted) {
    setAuthToken(persisted);
  }
};

bootstrapAuth();
const router = createRouter({ routeConfig, context: { queryClient } });
```

### 4. Form UX & Error Handling
- Display validation errors from `422` by reading `error.response.data.errors`.
- Show a specific message for `invalid_credentials`.
- Implement exponential backoff for `429` using the `retry-after` header surfaced in the interceptor.
- Consider a global notification or banner for when rate limits are reached.

### 5. Logout & Token Revocation
- Always call `POST /auth/logout` before clearing local state.
- Flush cached queries with the `['auth']` prefix and any sensitive domain data.
- Optionally, reinitialize the router context to force protected routes back to the login page.

## TanStack Router Integration

### Route Protection
Use route loaders/guards to require authentication for private areas. `ensureQueryData` lets you reuse the cached user details.

```ts
// src/routes/dashboard.tsx
import { createFileRoute, redirect } from '@tanstack/router';
import { queryClient } from '@/api/queryClient';
import { fetchMe } from '@/features/auth/api';

export const Route = createFileRoute('/dashboard')({
  loader: async () => {
    try {
      return await queryClient.ensureQueryData({ queryKey: ['auth', 'user'], queryFn: fetchMe });
    } catch (error: any) {
      if (error?.response?.status === 401) {
        throw redirect({ to: '/login', search: { redirect: '/dashboard' } });
      }
      throw error;
    }
  },
});
```

### Anonymous-Only Screens
Use the opposite guard to keep authenticated users out of login/register pages.

```ts
// src/routes/login.tsx
export const Route = createFileRoute('/login')({
  loader: async () => {
    try {
      await queryClient.ensureQueryData({ queryKey: ['auth', 'user'], queryFn: fetchMe });
      throw redirect({ to: '/dashboard' });
    } catch (error: any) {
      if (error?.response?.status === 401) {
        return null; // stay on login
      }
      throw error;
    }
  },
});
```

## Security & Operational Notes
- Tokens remain valid until explicitly revoked. Encourage users to log out on shared devices; consider future enhancements (token TTLs, phone-number-first login).
- Prefix `Authorization` headers with `Bearer` exactly; the backend deletes any existing token named `<user-id>-spa` before issuing a fresh one, so re-login refreshes the device session.
- Use HTTPS everywhere; never send credentials over insecure transport.
- When adding phone-number OTP or additional devices, reuse the same bearer token flow but extend the login payload/validation rules.
- Monitor `429` rates and surface telemetry (e.g. to Sentry) so the team can adjust the limiter buckets if necessary.

## Profile Management

### GET /api/v1/profile
- Requires `Authorization: Bearer <token>`
- Returns the same `UserResource` shape described in the login response.

### PATCH /api/v1/profile
- Requires `Authorization: Bearer <token>`
- Content-Type: `application/json`
- Body schema (TypeScript types):

```ts
interface UpdateProfilePayload {
  first_name: string;      // required, ≤ 255 chars
  last_name: string;       // required, ≤ 255 chars
  phone_number: string;    // required, exactly 10 digits, must start with 09 or 07
  email: string;           // required, unique, lowercase, valid email
}
```

- Successful `200 OK` returns the updated user object in `UserResource` form. Changing `email` resets `email_verified_at` to `null` until the user re-verifies.
- Validation errors follow Laravel's JSON format. Pre-flight client-side checks should mirror the server rules (e.g., enforce digit-only phone numbers, lowercase email).

### React Query Usage
- Extend the `useAuthUser` hook to invalidate or refetch after profile updates:

```ts
export function useUpdateProfile() {
  return useMutation({
    mutationFn: (payload: UpdateProfilePayload) => api.patch<User>('/profile', payload).then((res) => res.data),
    onSuccess: (user) => {
      queryClient.setQueryData(['auth', 'user'], user);
    },
  });
}
```

- Handle `422` responses to surface field-level errors, and note that rate limiting (`429`) still applies.

## QA Checklist
- ✅ Successful login stores the token, preloads `/auth/me`, and redirects the user.
- ✅ Invalid credentials surface the 422 error without clearing previous form input.
- ✅ Refreshing the app with a stored token keeps the session alive and avoids a flash of unauthenticated UI.
- ✅ Logout revokes the token and clears all React Query caches.
- ✅ Hitting the limiter produces a useful UI message and respects the `retry-after` guidance.
