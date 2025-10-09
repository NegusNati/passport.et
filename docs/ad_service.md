# Advertisement Service API - Frontend Implementation Guide

Complete API reference and implementation guide for integrating advertisement functionality into your React frontend, covering both the admin dashboard CRM and public ad slot displays.

---

## Table of Contents

- [Overview](#overview)
- [TypeScript Types & Interfaces](#typescript-types--interfaces)
- [Authentication](#authentication)
- [Public API (Client-Side Ad Slots)](#public-api-client-side-ad-slots)
- [Admin API (CRM Dashboard)](#admin-api-crm-dashboard)
- [React Implementation Examples](#react-implementation-examples)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)
- [Testing](#testing)

---

## Overview

### Base URL
```
Production: https://api.passport.et/api/v1
Development: http://localhost:8000/api/v1
```

### Authentication
- **Public endpoints** (ad display, tracking): No authentication required
- **Admin endpoints** (CRM): Requires Sanctum Bearer token + `manage-advertisements` permission

### Rate Limits
- Anonymous: 60 requests/minute
- Authenticated: 120 requests/minute
- Premium: 240 requests/minute

---

## TypeScript Types & Interfaces

### Core Types

```typescript
// Advertisement Status
type AdStatus = 'draft' | 'active' | 'paused' | 'expired' | 'scheduled';

// Payment Status
type PaymentStatus = 'pending' | 'paid' | 'refunded' | 'failed';

// Package Type
type PackageType = 'weekly' | 'monthly' | 'yearly';

// Advertisement Interface (Public)
interface Advertisement {
  id: number;
  ad_slot_number: string;
  ad_title: string;
  ad_desc: string | null;
  ad_excerpt: string | null;
  ad_desktop_asset: string | null; // Full URL
  ad_mobile_asset: string | null;  // Full URL
  ad_client_link: string | null;
  status: AdStatus;
  package_type: PackageType;
  ad_published_date: string | null; // ISO date string
  ad_ending_date: string | null;    // ISO date string
  payment_status: PaymentStatus;
  payment_amount: string; // Decimal string
  client_name: string | null;
  impressions_count: number;
  clicks_count: number;
  priority: number;
  created_at: string; // ISO 8601 timestamp
  updated_at: string; // ISO 8601 timestamp
}

// Advertisement Interface (Admin - includes extra fields)
interface AdvertisementAdmin extends Advertisement {
  admin_notes: string | null;
  expiry_notification_sent: boolean;
  days_until_expiry: number | null;
  is_active: boolean;
  is_expired: boolean;
  advertisement_request_id: number | null;
}

// Paginated Response
interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    to: number;
    per_page: number;
    total: number;
    last_page: number;
    has_more: boolean;
  };
  filters?: Record<string, any>;
}

// Simple Collection Response (Non-paginated)
interface CollectionResponse<T> {
  data: T[];
  meta: {
    count: number;
  };
}

// Statistics Response
interface AdStatistics {
  data: {
    total_active: number;
    expiring_soon: number;
    expired_pending_renewal: number;
    total_impressions: number;
    total_clicks: number;
    avg_ctr: number; // Percentage
    revenue_this_month: number;
  };
}

// Search/Filter Parameters (Admin)
interface AdSearchParams {
  ad_title?: string;
  ad_slot_number?: string;
  client_name?: string;
  status?: AdStatus;
  payment_status?: PaymentStatus;
  package_type?: PackageType;
  published_after?: string; // YYYY-MM-DD
  published_before?: string; // YYYY-MM-DD
  ending_after?: string; // YYYY-MM-DD
  ending_before?: string; // YYYY-MM-DD
  sort?: string; // Column name
  sort_dir?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
}

// Create/Update Advertisement (Admin)
interface CreateAdvertisementPayload {
  ad_slot_number: string;
  ad_title: string;
  ad_desc?: string;
  ad_excerpt?: string;
  ad_desktop_asset?: File | string; // File for upload or URL
  ad_mobile_asset?: File | string;
  ad_client_link?: string;
  client_name?: string;
  package_type: PackageType;
  ad_published_date: string; // YYYY-MM-DD
  ad_ending_date?: string; // YYYY-MM-DD
  status: AdStatus;
  payment_status: PaymentStatus;
  payment_amount: number;
  priority?: number; // 0-100
  admin_notes?: string;
  advertisement_request_id?: number;
}

// Update is partial (all fields optional)
type UpdateAdvertisementPayload = Partial<CreateAdvertisementPayload>;

// Error Response
interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
```

---

## Public API (Client-Side Ad Slots)

### 1. Get Active Advertisements

Fetch all currently active ads for display on the frontend.

**Endpoint:** `GET /api/v1/advertisements/active`

**Query Parameters:**
- `slot_number` (optional) - Filter by specific ad slot

**TypeScript:**
```typescript
interface GetActiveAdsParams {
  slot_number?: string;
}

async function getActiveAds(
  params?: GetActiveAdsParams
): Promise<CollectionResponse<Advertisement>> {
  const queryString = params?.slot_number 
    ? `?slot_number=${encodeURIComponent(params.slot_number)}`
    : '';
    
  const response = await fetch(
    `${API_BASE_URL}/advertisements/active${queryString}`,
    {
      headers: {
        'Accept': 'application/json',
      },
    }
  );
  
  if (!response.ok) {
    throw new Error('Failed to fetch active ads');
  }
  
  return response.json();
}
```

**Response Example:**
```json
{
  "data": [
    {
      "id": 1,
      "ad_slot_number": "homepage-banner-1",
      "ad_title": "Summer Sale 2025",
      "ad_excerpt": "Get 50% off on all items",
      "ad_desktop_asset": "https://cdn.example.com/ads/summer-desktop.jpg",
      "ad_mobile_asset": "https://cdn.example.com/ads/summer-mobile.jpg",
      "ad_client_link": "https://shop.example.com/summer-sale",
      "priority": 10,
      "impressions_count": 125000,
      "clicks_count": 3500
    }
  ],
  "meta": {
    "count": 1
  }
}
```

**React Component Example:**
```tsx
// AdSlot.tsx
import React, { useEffect, useState } from 'react';
import { getActiveAds } from '@/api/advertisements';

interface AdSlotProps {
  slotNumber: string;
  className?: string;
}

export const AdSlot: React.FC<AdSlotProps> = ({ slotNumber, className }) => {
  const [ad, setAd] = useState<Advertisement | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchAd() {
      try {
        const response = await getActiveAds({ slot_number: slotNumber });
        
        if (response.data.length > 0) {
          setAd(response.data[0]); // Take highest priority ad
        }
      } catch (error) {
        console.error('Failed to load advertisement:', error);
      } finally {
        setLoading(false);
      }
    }

    fetchAd();
  }, [slotNumber]);

  if (loading) return <div className={className}>Loading...</div>;
  if (!ad) return null;

  return (
    <div className={className}>
      <AdDisplay ad={ad} />
    </div>
  );
};
```

---

### 2. Track Advertisement Impression

Record when an advertisement is displayed to a user.

**Endpoint:** `POST /api/v1/advertisements/{id}/impression`

**Body:**
```typescript
interface TrackImpressionPayload {
  session_id?: string; // Optional unique session identifier
}
```

**TypeScript:**
```typescript
async function trackImpression(
  adId: number, 
  sessionId?: string
): Promise<void> {
  await fetch(`${API_BASE_URL}/advertisements/${adId}/impression`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ session_id: sessionId }),
  });
  
  // Fire and forget - ignore response
}
```

**React Hook Example:**
```tsx
// useAdImpression.ts
import { useEffect, useRef } from 'react';
import { trackImpression } from '@/api/advertisements';

export function useAdImpression(adId: number | null) {
  const tracked = useRef(false);
  
  useEffect(() => {
    if (!adId || tracked.current) return;
    
    // Get or generate session ID
    const sessionId = sessionStorage.getItem('ad_session_id') || 
      crypto.randomUUID();
    sessionStorage.setItem('ad_session_id', sessionId);
    
    // Use IntersectionObserver to track only when visible
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !tracked.current) {
            trackImpression(adId, sessionId);
            tracked.current = true;
          }
        });
      },
      { threshold: 0.5 } // 50% visible
    );
    
    const element = document.getElementById(`ad-${adId}`);
    if (element) observer.observe(element);
    
    return () => observer.disconnect();
  }, [adId]);
}
```

**Response:** `204 No Content`

**Notes:**
- Asynchronous processing (queued job)
- 10-second deduplication window per session+ad
- Rate limit: 240 requests/minute per IP

---

### 3. Track Advertisement Click

Record when a user clicks on an advertisement.

**Endpoint:** `POST /api/v1/advertisements/{id}/click`

**Body:**
```typescript
interface TrackClickPayload {
  session_id?: string;
}
```

**TypeScript:**
```typescript
async function trackClick(
  adId: number, 
  sessionId?: string
): Promise<void> {
  await fetch(`${API_BASE_URL}/advertisements/${adId}/click`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ session_id: sessionId }),
  });
}
```

**React Component Example:**
```tsx
// AdDisplay.tsx
import React from 'react';
import { trackClick } from '@/api/advertisements';
import { useAdImpression } from '@/hooks/useAdImpression';

interface AdDisplayProps {
  ad: Advertisement;
}

export const AdDisplay: React.FC<AdDisplayProps> = ({ ad }) => {
  const isMobile = useMediaQuery('(max-width: 768px)');
  const assetUrl = isMobile ? ad.ad_mobile_asset : ad.ad_desktop_asset;
  
  // Track impression when visible
  useAdImpression(ad.id);
  
  const handleClick = async () => {
    const sessionId = sessionStorage.getItem('ad_session_id');
    
    // Track click
    await trackClick(ad.id, sessionId || undefined);
    
    // Navigate to client link
    if (ad.ad_client_link) {
      window.open(ad.ad_client_link, '_blank', 'noopener,noreferrer');
    }
  };

  return (
    <div 
      id={`ad-${ad.id}`}
      className="ad-container"
      onClick={handleClick}
      style={{ cursor: ad.ad_client_link ? 'pointer' : 'default' }}
    >
      {assetUrl && (
        <img 
          src={assetUrl} 
          alt={ad.ad_title}
          className="ad-image"
        />
      )}
      {ad.ad_excerpt && (
        <p className="ad-excerpt">{ad.ad_excerpt}</p>
      )}
    </div>
  );
};
```

**Response:** `204 No Content`

**Notes:**
- Asynchronous processing (queued job)
- 60-second deduplication window per session+ad
- Rate limit: 240 requests/minute per IP

---

## Admin API (CRM Dashboard)

All admin endpoints require authentication via Sanctum Bearer token and `manage-advertisements` permission.

**Headers Required:**
```typescript
const adminHeaders = {
  'Authorization': `Bearer ${token}`,
  'Accept': 'application/json',
  'Content-Type': 'application/json',
};
```

---

### 1. List/Search Advertisements

Get paginated list of all advertisements with advanced filtering.

**Endpoint:** `GET /api/v1/admin/advertisements`

**Query Parameters:**
```typescript
interface ListAdsParams {
  // Search filters
  ad_title?: string;          // Partial match
  ad_slot_number?: string;    // Exact match
  client_name?: string;       // Partial match
  status?: AdStatus;          // Exact match
  payment_status?: PaymentStatus;
  package_type?: PackageType;
  
  // Date filters
  published_after?: string;   // YYYY-MM-DD
  published_before?: string;  // YYYY-MM-DD
  ending_after?: string;      // YYYY-MM-DD
  ending_before?: string;     // YYYY-MM-DD
  
  // Sorting
  sort?: string;              // Column name
  sort_dir?: 'asc' | 'desc';
  
  // Pagination
  per_page?: number;          // Default: 20, Max: 100
  page?: number;              // Default: 1
}
```

**TypeScript:**
```typescript
async function listAdvertisements(
  params: ListAdsParams,
  token: string
): Promise<PaginatedResponse<AdvertisementAdmin>> {
  const queryParams = new URLSearchParams(
    Object.entries(params)
      .filter(([_, v]) => v !== undefined && v !== null)
      .map(([k, v]) => [k, String(v)])
  );
  
  const response = await fetch(
    `${API_BASE_URL}/admin/advertisements?${queryParams}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    }
  );
  
  if (!response.ok) {
    throw new Error('Failed to fetch advertisements');
  }
  
  return response.json();
}
```

**Response Example:**
```json
{
  "data": [
    {
      "id": 1,
      "ad_slot_number": "homepage-banner-1",
      "ad_title": "Summer Sale 2025",
      "ad_desc": "Full description of the summer sale promotion...",
      "ad_excerpt": "Get 50% off",
      "ad_desktop_asset": "https://cdn.example.com/ads/summer-desktop.jpg",
      "ad_mobile_asset": "https://cdn.example.com/ads/summer-mobile.jpg",
      "ad_client_link": "https://shop.example.com/summer-sale",
      "status": "active",
      "package_type": "monthly",
      "ad_published_date": "2025-01-01",
      "ad_ending_date": "2025-02-01",
      "payment_status": "paid",
      "payment_amount": "500.00",
      "client_name": "ACME Corporation",
      "impressions_count": 125000,
      "clicks_count": 3500,
      "priority": 10,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-15T12:30:45.000000Z",
      "admin_notes": "Approved by marketing team",
      "expiry_notification_sent": false,
      "days_until_expiry": 17,
      "is_active": true,
      "is_expired": false,
      "advertisement_request_id": 42
    }
  ],
  "links": {
    "first": "http://api.example.com/admin/advertisements?page=1",
    "last": "http://api.example.com/admin/advertisements?page=5",
    "prev": null,
    "next": "http://api.example.com/admin/advertisements?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "to": 20,
    "per_page": 20,
    "total": 87,
    "last_page": 5,
    "has_more": true
  },
  "filters": {
    "status": "active"
  }
}
```

**React Hook Example:**
```tsx
// useAdvertisements.ts
import { useQuery } from '@tanstack/react-query';

export function useAdvertisements(params: ListAdsParams) {
  return useQuery({
    queryKey: ['advertisements', params],
    queryFn: () => listAdvertisements(params, getToken()),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

// In component:
const { data, isLoading, error } = useAdvertisements({
  status: 'active',
  page: currentPage,
  per_page: 20,
  sort: 'created_at',
  sort_dir: 'desc',
});
```

---

### 2. Get Advertisement Details

Retrieve a single advertisement by ID.

**Endpoint:** `GET /api/v1/admin/advertisements/{id}`

**TypeScript:**
```typescript
async function getAdvertisement(
  id: number,
  token: string
): Promise<{ data: AdvertisementAdmin }> {
  const response = await fetch(
    `${API_BASE_URL}/admin/advertisements/${id}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    }
  );
  
  if (!response.ok) {
    if (response.status === 404) {
      throw new Error('Advertisement not found');
    }
    throw new Error('Failed to fetch advertisement');
  }
  
  return response.json();
}
```

**Response Example:**
```json
{
  "data": {
    "id": 1,
    "ad_slot_number": "homepage-banner-1",
    // ... (same as list response)
  }
}
```

---

### 3. Create Advertisement

Create a new advertisement in the CRM.

**Endpoint:** `POST /api/v1/admin/advertisements`

**Content-Type:** `multipart/form-data` (if uploading files) or `application/json`

**TypeScript:**
```typescript
async function createAdvertisement(
  payload: CreateAdvertisementPayload,
  token: string
): Promise<{ data: AdvertisementAdmin }> {
  const formData = new FormData();
  
  // Add all fields to FormData
  Object.entries(payload).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      if (value instanceof File) {
        formData.append(key, value);
      } else {
        formData.append(key, String(value));
      }
    }
  });
  
  const response = await fetch(
    `${API_BASE_URL}/admin/advertisements`,
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        // Don't set Content-Type - browser sets it with boundary
      },
      body: formData,
    }
  );
  
  if (!response.ok) {
    const error: ApiError = await response.json();
    throw error;
  }
  
  return response.json();
}
```

**Request Example (JSON):**
```json
{
  "ad_slot_number": "homepage-banner-2",
  "ad_title": "Winter Collection",
  "ad_desc": "Explore our new winter collection with exclusive designs",
  "ad_excerpt": "New arrivals - 30% off",
  "ad_desktop_asset": "https://cdn.example.com/winter-desktop.jpg",
  "ad_mobile_asset": "https://cdn.example.com/winter-mobile.jpg",
  "ad_client_link": "https://shop.example.com/winter",
  "client_name": "Fashion Boutique Ltd",
  "package_type": "monthly",
  "ad_published_date": "2025-12-01",
  "ad_ending_date": "2025-12-31",
  "status": "scheduled",
  "payment_status": "pending",
  "payment_amount": 750.00,
  "priority": 5,
  "admin_notes": "Approved by sales team"
}
```

**Response:** `201 Created`

**Validation Rules:**
- `ad_slot_number`: Required, unique, max 50 chars
- `ad_title`: Required, max 255 chars
- `ad_desc`: Optional, max 2000 chars
- `ad_excerpt`: Optional, max 500 chars
- `ad_desktop_asset`: File (jpg,jpeg,png,gif,svg,mp4,webp, max 10MB) or URL
- `ad_mobile_asset`: File (jpg,jpeg,png,gif,svg,mp4,webp, max 10MB) or URL
- `ad_client_link`: Optional, valid URL, max 255 chars
- `package_type`: Required, one of: weekly, monthly, yearly
- `ad_published_date`: Required, date (today or future)
- `ad_ending_date`: Optional, date, must be after published_date
- `status`: Required, one of: draft, active, paused, scheduled
- `payment_status`: Required, one of: pending, paid, refunded, failed
- `payment_amount`: Required, numeric, min 0, max 999999.99
- `priority`: Optional, integer 0-100

**Business Rules:**
- If `ad_published_date` is in the future, status auto-set to `scheduled`
- Cannot activate ad (`status: 'active'`) if `payment_status: 'pending'`

**React Form Example:**
```tsx
// CreateAdForm.tsx
import { useForm } from 'react-hook-form';
import { useMutation, useQueryClient } from '@tanstack/react-query';

export const CreateAdForm: React.FC = () => {
  const { register, handleSubmit, formState: { errors } } = useForm<CreateAdvertisementPayload>();
  const queryClient = useQueryClient();
  
  const createMutation = useMutation({
    mutationFn: (data: CreateAdvertisementPayload) => 
      createAdvertisement(data, getToken()),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['advertisements'] });
      toast.success('Advertisement created successfully');
    },
    onError: (error: ApiError) => {
      toast.error(error.message);
    },
  });
  
  return (
    <form onSubmit={handleSubmit((data) => createMutation.mutate(data))}>
      <input
        {...register('ad_slot_number', { required: true })}
        placeholder="Ad Slot Number"
      />
      {errors.ad_slot_number && <span>Required</span>}
      
      <input
        {...register('ad_title', { required: true })}
        placeholder="Title"
      />
      
      <select {...register('package_type', { required: true })}>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
      </select>
      
      <input
        type="date"
        {...register('ad_published_date', { required: true })}
      />
      
      <input
        type="date"
        {...register('ad_ending_date')}
      />
      
      <input
        type="file"
        accept="image/*,video/mp4"
        {...register('ad_desktop_asset')}
      />
      
      <select {...register('status', { required: true })}>
        <option value="draft">Draft</option>
        <option value="scheduled">Scheduled</option>
        <option value="active">Active</option>
      </select>
      
      <select {...register('payment_status', { required: true })}>
        <option value="pending">Pending</option>
        <option value="paid">Paid</option>
      </select>
      
      <input
        type="number"
        step="0.01"
        {...register('payment_amount', { required: true, min: 0 })}
        placeholder="Payment Amount"
      />
      
      <button type="submit" disabled={createMutation.isPending}>
        {createMutation.isPending ? 'Creating...' : 'Create Advertisement'}
      </button>
    </form>
  );
};
```

---

### 4. Update Advertisement

Update an existing advertisement (all fields optional).

**Endpoint:** `PATCH /api/v1/admin/advertisements/{id}`

**TypeScript:**
```typescript
async function updateAdvertisement(
  id: number,
  payload: UpdateAdvertisementPayload,
  token: string
): Promise<{ data: AdvertisementAdmin }> {
  const formData = new FormData();
  
  Object.entries(payload).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      if (value instanceof File) {
        formData.append(key, value);
      } else {
        formData.append(key, String(value));
      }
    }
  });
  
  const response = await fetch(
    `${API_BASE_URL}/admin/advertisements/${id}`,
    {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
      body: formData,
    }
  );
  
  if (!response.ok) {
    const error: ApiError = await response.json();
    throw error;
  }
  
  return response.json();
}
```

**Request Example:**
```json
{
  "status": "active",
  "payment_status": "paid",
  "admin_notes": "Payment confirmed, activated on 2025-01-15"
}
```

**Response:** `200 OK`

**Additional Business Rules:**
- If status changes to `active` and `ad_published_date` is null, it's set to today
- If `ad_ending_date` changes, `expiry_notification_sent` resets to `false`
- Cannot set `status: 'active'` if `payment_status: 'pending'` (auto-corrected to `scheduled`)
- Uploading new asset files deletes old files

---

### 5. Delete Advertisement

Soft delete an advertisement.

**Endpoint:** `DELETE /api/v1/admin/advertisements/{id}`

**TypeScript:**
```typescript
async function deleteAdvertisement(
  id: number,
  token: string
): Promise<void> {
  const response = await fetch(
    `${API_BASE_URL}/admin/advertisements/${id}`,
    {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    }
  );
  
  if (!response.ok) {
    throw new Error('Failed to delete advertisement');
  }
}
```

**Response:** `204 No Content`

**Side Effects:**
- Associated asset files deleted from storage
- Cache automatically flushed

---

### 6. Restore Advertisement

Restore a soft-deleted advertisement.

**Endpoint:** `POST /api/v1/admin/advertisements/{id}/restore`

**TypeScript:**
```typescript
async function restoreAdvertisement(
  id: number,
  token: string
): Promise<{ data: AdvertisementAdmin }> {
  const response = await fetch(
    `${API_BASE_URL}/admin/advertisements/${id}/restore`,
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    }
  );
  
  if (!response.ok) {
    throw new Error('Failed to restore advertisement');
  }
  
  return response.json();
}
```

**Response:** `200 OK`

---

### 7. Get Dashboard Statistics

Retrieve aggregated statistics for the advertisements dashboard.

**Endpoint:** `GET /api/v1/admin/advertisements/stats`

**TypeScript:**
```typescript
async function getAdStatistics(
  token: string
): Promise<AdStatistics> {
  const response = await fetch(
    `${API_BASE_URL}/admin/advertisements/stats`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    }
  );
  
  if (!response.ok) {
    throw new Error('Failed to fetch statistics');
  }
  
  return response.json();
}
```

**Response Example:**
```json
{
  "data": {
    "total_active": 15,
    "expiring_soon": 3,
    "expired_pending_renewal": 5,
    "total_impressions": 1250000,
    "total_clicks": 35000,
    "avg_ctr": 2.8,
    "revenue_this_month": 12500.00
  }
}
```

**React Dashboard Component:**
```tsx
// AdStatsDashboard.tsx
import { useQuery } from '@tanstack/react-query';

export const AdStatsDashboard: React.FC = () => {
  const { data, isLoading } = useQuery({
    queryKey: ['ad-stats'],
    queryFn: () => getAdStatistics(getToken()),
    refetchInterval: 5 * 60 * 1000, // Refresh every 5 minutes
  });
  
  if (isLoading) return <div>Loading...</div>;
  
  const stats = data?.data;
  
  return (
    <div className="stats-grid">
      <StatCard 
        title="Active Ads" 
        value={stats?.total_active} 
        icon="📊"
      />
      <StatCard 
        title="Expiring Soon" 
        value={stats?.expiring_soon} 
        icon="⚠️"
        variant="warning"
      />
      <StatCard 
        title="Expired (Renewal Needed)" 
        value={stats?.expired_pending_renewal} 
        icon="🔴"
        variant="danger"
      />
      <StatCard 
        title="Total Impressions" 
        value={stats?.total_impressions.toLocaleString()} 
        icon="👁️"
      />
      <StatCard 
        title="Total Clicks" 
        value={stats?.total_clicks.toLocaleString()} 
        icon="🖱️"
      />
      <StatCard 
        title="Average CTR" 
        value={`${stats?.avg_ctr}%`} 
        icon="📈"
      />
      <StatCard 
        title="Revenue This Month" 
        value={`$${stats?.revenue_this_month.toLocaleString()}`} 
        icon="💰"
        variant="success"
      />
    </div>
  );
};
```

---

## React Implementation Examples

### Complete API Client Module

```typescript
// api/advertisements.ts
import axios, { AxiosInstance } from 'axios';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';

// Create axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Accept': 'application/json',
  },
});

// Add auth interceptor
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Public API
export const publicAdApi = {
  getActive: (params?: { slot_number?: string }) =>
    apiClient.get<CollectionResponse<Advertisement>>('/advertisements/active', { params }),
    
  trackImpression: (adId: number, sessionId?: string) =>
    apiClient.post(`/advertisements/${adId}/impression`, { session_id: sessionId }),
    
  trackClick: (adId: number, sessionId?: string) =>
    apiClient.post(`/advertisements/${adId}/click`, { session_id: sessionId }),
};

// Admin API
export const adminAdApi = {
  list: (params: ListAdsParams) =>
    apiClient.get<PaginatedResponse<AdvertisementAdmin>>('/admin/advertisements', { params }),
    
  get: (id: number) =>
    apiClient.get<{ data: AdvertisementAdmin }>(`/admin/advertisements/${id}`),
    
  create: (payload: CreateAdvertisementPayload) => {
    const formData = new FormData();
    Object.entries(payload).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value instanceof File ? value : String(value));
      }
    });
    return apiClient.post<{ data: AdvertisementAdmin }>('/admin/advertisements', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
    
  update: (id: number, payload: UpdateAdvertisementPayload) => {
    const formData = new FormData();
    Object.entries(payload).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value instanceof File ? value : String(value));
      }
    });
    return apiClient.patch<{ data: AdvertisementAdmin }>(
      `/admin/advertisements/${id}`, 
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    );
  },
    
  delete: (id: number) =>
    apiClient.delete(`/admin/advertisements/${id}`),
    
  restore: (id: number) =>
    apiClient.post<{ data: AdvertisementAdmin }>(`/admin/advertisements/${id}/restore`),
    
  getStats: () =>
    apiClient.get<AdStatistics>('/admin/advertisements/stats'),
};
```

---

### Admin Dashboard - Data Table Component

```tsx
// components/admin/AdvertisementTable.tsx
import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { adminAdApi } from '@/api/advertisements';

export const AdvertisementTable: React.FC = () => {
  const [filters, setFilters] = useState<ListAdsParams>({
    page: 1,
    per_page: 20,
    sort: 'created_at',
    sort_dir: 'desc',
  });
  
  const queryClient = useQueryClient();
  
  // Fetch advertisements
  const { data, isLoading, error } = useQuery({
    queryKey: ['advertisements', filters],
    queryFn: () => adminAdApi.list(filters).then(res => res.data),
  });
  
  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: adminAdApi.delete,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['advertisements'] });
      toast.success('Advertisement deleted');
    },
  });
  
  // Status badge helper
  const getStatusBadge = (status: AdStatus) => {
    const variants = {
      draft: 'bg-gray-200 text-gray-800',
      active: 'bg-green-200 text-green-800',
      paused: 'bg-yellow-200 text-yellow-800',
      expired: 'bg-red-200 text-red-800',
      scheduled: 'bg-blue-200 text-blue-800',
    };
    
    return (
      <span className={`px-2 py-1 rounded text-xs font-semibold ${variants[status]}`}>
        {status.toUpperCase()}
      </span>
    );
  };
  
  if (isLoading) return <div>Loading advertisements...</div>;
  if (error) return <div>Error loading advertisements</div>;
  
  return (
    <div>
      {/* Filters */}
      <div className="filters mb-4">
        <input
          type="text"
          placeholder="Search by title"
          value={filters.ad_title || ''}
          onChange={(e) => setFilters({ ...filters, ad_title: e.target.value, page: 1 })}
          className="border p-2 rounded"
        />
        
        <select
          value={filters.status || ''}
          onChange={(e) => setFilters({ ...filters, status: e.target.value as AdStatus, page: 1 })}
          className="border p-2 rounded ml-2"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="active">Active</option>
          <option value="scheduled">Scheduled</option>
          <option value="paused">Paused</option>
          <option value="expired">Expired</option>
        </select>
        
        <select
          value={filters.payment_status || ''}
          onChange={(e) => setFilters({ ...filters, payment_status: e.target.value as PaymentStatus, page: 1 })}
          className="border p-2 rounded ml-2"
        >
          <option value="">All Payment Statuses</option>
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
          <option value="refunded">Refunded</option>
          <option value="failed">Failed</option>
        </select>
      </div>
      
      {/* Table */}
      <table className="min-w-full border">
        <thead>
          <tr className="bg-gray-100">
            <th className="p-2 text-left">Slot</th>
            <th className="p-2 text-left">Title</th>
            <th className="p-2 text-left">Client</th>
            <th className="p-2 text-left">Status</th>
            <th className="p-2 text-left">Package</th>
            <th className="p-2 text-left">Published</th>
            <th className="p-2 text-left">Expires</th>
            <th className="p-2 text-right">Impressions</th>
            <th className="p-2 text-right">Clicks</th>
            <th className="p-2 text-right">CTR</th>
            <th className="p-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          {data?.data.map((ad) => {
            const ctr = ad.impressions_count > 0 
              ? ((ad.clicks_count / ad.impressions_count) * 100).toFixed(2)
              : '0.00';
            
            return (
              <tr key={ad.id} className="border-b hover:bg-gray-50">
                <td className="p-2 font-mono text-sm">{ad.ad_slot_number}</td>
                <td className="p-2">{ad.ad_title}</td>
                <td className="p-2">{ad.client_name || '-'}</td>
                <td className="p-2">{getStatusBadge(ad.status)}</td>
                <td className="p-2 capitalize">{ad.package_type}</td>
                <td className="p-2">{ad.ad_published_date || '-'}</td>
                <td className="p-2">
                  {ad.ad_ending_date || '-'}
                  {ad.days_until_expiry !== null && ad.days_until_expiry <= 3 && (
                    <span className="ml-2 text-red-600 text-xs">
                      ({ad.days_until_expiry}d left)
                    </span>
                  )}
                </td>
                <td className="p-2 text-right">{ad.impressions_count.toLocaleString()}</td>
                <td className="p-2 text-right">{ad.clicks_count.toLocaleString()}</td>
                <td className="p-2 text-right">{ctr}%</td>
                <td className="p-2">
                  <button
                    onClick={() => navigateTo(`/admin/ads/${ad.id}`)}
                    className="text-blue-600 mr-2"
                  >
                    Edit
                  </button>
                  <button
                    onClick={() => {
                      if (confirm('Delete this ad?')) {
                        deleteMutation.mutate(ad.id);
                      }
                    }}
                    className="text-red-600"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            );
          })}
        </tbody>
      </table>
      
      {/* Pagination */}
      {data?.meta && (
        <div className="flex items-center justify-between mt-4">
          <div className="text-sm text-gray-600">
            Showing {data.meta.from} to {data.meta.to} of {data.meta.total} results
          </div>
          
          <div className="flex gap-2">
            <button
              onClick={() => setFilters({ ...filters, page: filters.page! - 1 })}
              disabled={!data.links.prev}
              className="px-4 py-2 border rounded disabled:opacity-50"
            >
              Previous
            </button>
            
            <span className="px-4 py-2">
              Page {data.meta.current_page} of {data.meta.last_page}
            </span>
            
            <button
              onClick={() => setFilters({ ...filters, page: filters.page! + 1 })}
              disabled={!data.links.next}
              className="px-4 py-2 border rounded disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      )}
    </div>
  );
};
```

---

### Client-Side Ad Slot Implementation

```tsx
// components/public/AdSlot.tsx
import React, { useEffect, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { publicAdApi } from '@/api/advertisements';
import { useAdImpression } from '@/hooks/useAdImpression';

interface AdSlotProps {
  slotNumber: string;
  className?: string;
  fallback?: React.ReactNode;
}

export const AdSlot: React.FC<AdSlotProps> = ({ 
  slotNumber, 
  className = '', 
  fallback 
}) => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['active-ads', slotNumber],
    queryFn: () => publicAdApi.getActive({ slot_number: slotNumber }).then(res => res.data),
    staleTime: 5 * 60 * 1000, // Cache for 5 minutes
    retry: 2,
  });
  
  const ad = data?.data[0]; // Get first (highest priority) ad for this slot
  
  // Track impression when visible
  useAdImpression(ad?.id || null);
  
  const handleAdClick = async () => {
    if (!ad) return;
    
    const sessionId = sessionStorage.getItem('ad_session_id') || crypto.randomUUID();
    sessionStorage.setItem('ad_session_id', sessionId);
    
    try {
      await publicAdApi.trackClick(ad.id, sessionId);
    } catch (error) {
      console.error('Failed to track click:', error);
    }
    
    // Navigate to client link
    if (ad.ad_client_link) {
      window.open(ad.ad_client_link, '_blank', 'noopener,noreferrer');
    }
  };
  
  if (isLoading) {
    return <div className={`ad-slot-loading ${className}`}>Loading ad...</div>;
  }
  
  if (error || !ad) {
    return fallback ? <>{fallback}</> : null;
  }
  
  const isMobile = window.innerWidth < 768;
  const assetUrl = isMobile ? ad.ad_mobile_asset : ad.ad_desktop_asset;
  
  return (
    <div 
      id={`ad-${ad.id}`}
      className={`ad-slot ${className}`}
      onClick={handleAdClick}
      role="button"
      tabIndex={0}
      onKeyDown={(e) => e.key === 'Enter' && handleAdClick()}
      style={{ 
        cursor: ad.ad_client_link ? 'pointer' : 'default',
      }}
    >
      {assetUrl && (
        <div className="ad-asset-container">
          {assetUrl.endsWith('.mp4') ? (
            <video 
              src={assetUrl} 
              autoPlay 
              loop 
              muted 
              playsInline
              className="ad-video"
            />
          ) : (
            <img 
              src={assetUrl} 
              alt={ad.ad_title}
              className="ad-image"
              loading="lazy"
            />
          )}
        </div>
      )}
      
      {ad.ad_excerpt && (
        <div className="ad-content">
          <p className="ad-excerpt">{ad.ad_excerpt}</p>
        </div>
      )}
      
      <span className="ad-label">Advertisement</span>
    </div>
  );
};

// Usage in pages:
// <AdSlot slotNumber="homepage-banner-1" className="my-4" />
// <AdSlot slotNumber="sidebar-ad-1" className="sticky top-4" />
```

---

### Admin Edit Form Component

```tsx
// components/admin/EditAdForm.tsx
import React from 'react';
import { useForm } from 'react-hook-form';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { adminAdApi } from '@/api/advertisements';

interface EditAdFormProps {
  advertisementId: number;
  onSuccess?: () => void;
}

export const EditAdForm: React.FC<EditAdFormProps> = ({ advertisementId, onSuccess }) => {
  const queryClient = useQueryClient();
  
  // Fetch existing ad data
  const { data: adData, isLoading } = useQuery({
    queryKey: ['advertisement', advertisementId],
    queryFn: () => adminAdApi.get(advertisementId).then(res => res.data.data),
  });
  
  const { register, handleSubmit, formState: { errors }, watch } = useForm<UpdateAdvertisementPayload>({
    values: adData, // Auto-populate form
  });
  
  const updateMutation = useMutation({
    mutationFn: (data: UpdateAdvertisementPayload) => 
      adminAdApi.update(advertisementId, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['advertisements'] });
      queryClient.invalidateQueries({ queryKey: ['advertisement', advertisementId] });
      toast.success('Advertisement updated successfully');
      onSuccess?.();
    },
    onError: (error: any) => {
      const apiError = error.response?.data as ApiError;
      toast.error(apiError?.message || 'Failed to update advertisement');
    },
  });
  
  const deleteMutation = useMutation({
    mutationFn: () => adminAdApi.delete(advertisementId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['advertisements'] });
      toast.success('Advertisement deleted');
      onSuccess?.();
    },
  });
  
  if (isLoading) return <div>Loading...</div>;
  
  const watchedStatus = watch('status');
  const watchedPaymentStatus = watch('payment_status');
  
  // Calculate CTR
  const ctr = adData?.impressions_count 
    ? ((adData.clicks_count / adData.impressions_count) * 100).toFixed(2)
    : '0.00';
  
  return (
    <form onSubmit={handleSubmit((data) => updateMutation.mutate(data))} className="space-y-4">
      {/* Performance Metrics (Read-only) */}
      <div className="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded">
        <div>
          <label className="text-sm text-gray-600">Impressions</label>
          <p className="text-2xl font-bold">{adData?.impressions_count.toLocaleString()}</p>
        </div>
        <div>
          <label className="text-sm text-gray-600">Clicks</label>
          <p className="text-2xl font-bold">{adData?.clicks_count.toLocaleString()}</p>
        </div>
        <div>
          <label className="text-sm text-gray-600">CTR</label>
          <p className="text-2xl font-bold">{ctr}%</p>
        </div>
      </div>
      
      {/* Expiry Warning */}
      {adData?.days_until_expiry !== null && adData.days_until_expiry <= 3 && (
        <div className="bg-yellow-50 border border-yellow-200 p-4 rounded">
          ⚠️ This ad expires in <strong>{adData.days_until_expiry} day(s)</strong>
        </div>
      )}
      
      {/* Basic Info */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium mb-1">Slot Number</label>
          <input
            {...register('ad_slot_number')}
            className="w-full border p-2 rounded"
            disabled // Usually shouldn't change slot after creation
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium mb-1">Priority (0-100)</label>
          <input
            type="number"
            {...register('priority', { min: 0, max: 100 })}
            className="w-full border p-2 rounded"
          />
        </div>
      </div>
      
      <div>
        <label className="block text-sm font-medium mb-1">Title *</label>
        <input
          {...register('ad_title', { required: true })}
          className="w-full border p-2 rounded"
        />
        {errors.ad_title && <span className="text-red-600 text-sm">Required</span>}
      </div>
      
      <div>
        <label className="block text-sm font-medium mb-1">Excerpt</label>
        <input
          {...register('ad_excerpt', { maxLength: 500 })}
          className="w-full border p-2 rounded"
          placeholder="Short text for display"
        />
      </div>
      
      <div>
        <label className="block text-sm font-medium mb-1">Description</label>
        <textarea
          {...register('ad_desc', { maxLength: 2000 })}
          rows={4}
          className="w-full border p-2 rounded"
        />
      </div>
      
      {/* Assets */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium mb-1">Desktop Asset</label>
          {adData?.ad_desktop_asset && (
            <img src={adData.ad_desktop_asset} alt="Current" className="mb-2 max-h-40" />
          )}
          <input
            type="file"
            {...register('ad_desktop_asset')}
            accept="image/*,video/mp4"
            className="w-full border p-2 rounded"
          />
          <span className="text-xs text-gray-500">Max 10MB</span>
        </div>
        
        <div>
          <label className="block text-sm font-medium mb-1">Mobile Asset</label>
          {adData?.ad_mobile_asset && (
            <img src={adData.ad_mobile_asset} alt="Current" className="mb-2 max-h-40" />
          )}
          <input
            type="file"
            {...register('ad_mobile_asset')}
            accept="image/*,video/mp4"
            className="w-full border p-2 rounded"
          />
          <span className="text-xs text-gray-500">Max 10MB</span>
        </div>
      </div>
      
      {/* Client Info */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium mb-1">Client Name</label>
          <input
            {...register('client_name')}
            className="w-full border p-2 rounded"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium mb-1">Client Link</label>
          <input
            type="url"
            {...register('ad_client_link')}
            className="w-full border p-2 rounded"
            placeholder="https://client.example.com"
          />
        </div>
      </div>
      
      {/* Package & Payment */}
      <div className="grid grid-cols-3 gap-4">
        <div>
          <label className="block text-sm font-medium mb-1">Package Type *</label>
          <select
            {...register('package_type', { required: true })}
            className="w-full border p-2 rounded"
          >
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
        
        <div>
          <label className="block text-sm font-medium mb-1">Payment Amount *</label>
          <input
            type="number"
            step="0.01"
            {...register('payment_amount', { required: true, min: 0 })}
            className="w-full border p-2 rounded"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium mb-1">Payment Status *</label>
          <select
            {...register('payment_status', { required: true })}
            className="w-full border p-2 rounded"
          >
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="refunded">Refunded</option>
            <option value="failed">Failed</option>
          </select>
        </div>
      </div>
      
      {/* Dates & Status */}
      <div className="grid grid-cols-3 gap-4">
        <div>
          <label className="block text-sm font-medium mb-1">Published Date *</label>
          <input
            type="date"
            {...register('ad_published_date', { required: true })}
            className="w-full border p-2 rounded"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium mb-1">Ending Date</label>
          <input
            type="date"
            {...register('ad_ending_date')}
            className="w-full border p-2 rounded"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium mb-1">Status *</label>
          <select
            {...register('status', { required: true })}
            className="w-full border p-2 rounded"
          >
            <option value="draft">Draft</option>
            <option value="scheduled">Scheduled</option>
            <option value="active">Active</option>
            <option value="paused">Paused</option>
            <option value="expired">Expired</option>
          </select>
          
          {/* Warning if trying to activate with pending payment */}
          {watchedStatus === 'active' && watchedPaymentStatus === 'pending' && (
            <span className="text-yellow-600 text-xs mt-1 block">
              ⚠️ Will be auto-corrected to "scheduled" (payment pending)
            </span>
          )}
        </div>
      </div>
      
      {/* Admin Notes */}
      <div>
        <label className="block text-sm font-medium mb-1">Admin Notes</label>
        <textarea
          {...register('admin_notes', { maxLength: 1000 })}
          rows={3}
          className="w-full border p-2 rounded"
          placeholder="Internal notes (not visible to clients)"
        />
      </div>
      
      {/* Actions */}
      <div className="flex gap-4">
        <button
          type="submit"
          disabled={updateMutation.isPending}
          className="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {updateMutation.isPending ? 'Saving...' : 'Save Changes'}
        </button>
        
        <button
          type="button"
          onClick={() => {
            if (confirm('Are you sure you want to delete this advertisement?')) {
              deleteMutation.mutate();
            }
          }}
          disabled={deleteMutation.isPending}
          className="px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50"
        >
          Delete
        </button>
      </div>
    </form>
  );
};
```

---

### Ad Slot Manager (Multiple Slots)

```tsx
// hooks/useAdSlots.ts
import { useQuery } from '@tanstack/react-query';
import { publicAdApi } from '@/api/advertisements';

// Define your ad slots
export const AD_SLOTS = {
  HOMEPAGE_BANNER: 'homepage-banner-1',
  HOMEPAGE_SIDEBAR: 'homepage-sidebar-1',
  ARTICLE_TOP: 'article-top-1',
  ARTICLE_BOTTOM: 'article-bottom-1',
  SIDEBAR_STICKY: 'sidebar-sticky-1',
} as const;

export type AdSlotId = typeof AD_SLOTS[keyof typeof AD_SLOTS];

export function useAdSlots() {
  return useQuery({
    queryKey: ['active-ads-all'],
    queryFn: () => publicAdApi.getActive().then(res => res.data),
    staleTime: 5 * 60 * 1000,
    select: (data) => {
      // Group ads by slot number for easy lookup
      const bySlot = new Map<string, Advertisement>();
      data.data.forEach(ad => {
        if (!bySlot.has(ad.ad_slot_number)) {
          bySlot.set(ad.ad_slot_number, ad);
        }
      });
      return bySlot;
    },
  });
}

// Usage:
const { data: adSlots } = useAdSlots();
const bannerAd = adSlots?.get(AD_SLOTS.HOMEPAGE_BANNER);
```

---

### Admin Bulk Actions Component

```tsx
// components/admin/BulkAdActions.tsx
import React from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { adminAdApi } from '@/api/advertisements';

interface BulkAdActionsProps {
  selectedIds: number[];
  onComplete: () => void;
}

export const BulkAdActions: React.FC<BulkAdActionsProps> = ({ selectedIds, onComplete }) => {
  const queryClient = useQueryClient();
  
  const bulkUpdateMutation = useMutation({
    mutationFn: async (updates: UpdateAdvertisementPayload) => {
      // Update multiple ads in parallel
      await Promise.all(
        selectedIds.map(id => adminAdApi.update(id, updates))
      );
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['advertisements'] });
      toast.success(`${selectedIds.length} advertisements updated`);
      onComplete();
    },
  });
  
  const bulkDeleteMutation = useMutation({
    mutationFn: async () => {
      await Promise.all(
        selectedIds.map(id => adminAdApi.delete(id))
      );
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['advertisements'] });
      toast.success(`${selectedIds.length} advertisements deleted`);
      onComplete();
    },
  });
  
  if (selectedIds.length === 0) return null;
  
  return (
    <div className="bg-blue-50 p-4 rounded flex items-center gap-4">
      <span className="font-medium">{selectedIds.length} selected</span>
      
      <button
        onClick={() => bulkUpdateMutation.mutate({ status: 'paused' })}
        className="px-4 py-2 bg-yellow-600 text-white rounded"
      >
        Pause All
      </button>
      
      <button
        onClick={() => bulkUpdateMutation.mutate({ status: 'active' })}
        className="px-4 py-2 bg-green-600 text-white rounded"
      >
        Activate All
      </button>
      
      <button
        onClick={() => {
          if (confirm(`Delete ${selectedIds.length} advertisements?`)) {
            bulkDeleteMutation.mutate();
          }
        }}
        className="px-4 py-2 bg-red-600 text-white rounded"
      >
        Delete All
      </button>
    </div>
  );
};
```

---

## Error Handling

### Standard Error Responses

```typescript
// 401 Unauthorized
{
  "message": "Unauthenticated."
}

// 403 Forbidden
{
  "message": "This action is unauthorized."
}

// 404 Not Found
{
  "message": "Advertisement not found."
}

// 422 Unprocessable Entity (Validation Error)
{
  "message": "The given data was invalid.",
  "errors": {
    "ad_slot_number": ["The ad slot number has already been taken."],
    "ad_ending_date": ["The ending date must be after the publication date."],
    "ad_desktop_asset": ["The ad desktop asset must not exceed 10MB."]
  }
}

// 429 Too Many Requests
{
  "status": "error",
  "code": "rate_limit_exceeded",
  "message": "Too many requests. Please slow down and try again shortly."
}
```

### Error Handler Utility

```typescript
// utils/apiErrorHandler.ts
export class ApiError extends Error {
  constructor(
    message: string,
    public statusCode: number,
    public errors?: Record<string, string[]>
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

export async function handleApiResponse<T>(response: Response): Promise<T> {
  if (response.ok) {
    return response.json();
  }
  
  const errorData = await response.json().catch(() => ({}));
  
  switch (response.status) {
    case 401:
      // Redirect to login
      window.location.href = '/login';
      throw new ApiError('Unauthenticated', 401);
      
    case 403:
      throw new ApiError('Unauthorized access', 403);
      
    case 404:
      throw new ApiError('Resource not found', 404);
      
    case 422:
      throw new ApiError(
        errorData.message || 'Validation failed',
        422,
        errorData.errors
      );
      
    case 429:
      throw new ApiError('Rate limit exceeded. Please try again later.', 429);
      
    default:
      throw new ApiError(
        errorData.message || 'An unexpected error occurred',
        response.status
      );
  }
}

// Usage:
const response = await fetch(url, options);
const data = await handleApiResponse<ResponseType>(response);
```

---

## Best Practices

### 1. Client-Side Ad Display

**Lazy Loading:**
```tsx
// Use intersection observer to load ads only when near viewport
import { LazyLoad } from 'react-lazyload';

<LazyLoad height={200} offset={100}>
  <AdSlot slotNumber="article-bottom-1" />
</LazyLoad>
```

**Session Management:**
```typescript
// Generate unique session ID once per browser session
function getOrCreateSessionId(): string {
  let sessionId = sessionStorage.getItem('ad_session_id');
  
  if (!sessionId) {
    sessionId = crypto.randomUUID();
    sessionStorage.setItem('ad_session_id', sessionId);
  }
  
  return sessionId;
}
```

**Responsive Assets:**
```tsx
// Use picture element for better performance
<picture>
  <source 
    media="(max-width: 768px)" 
    srcSet={ad.ad_mobile_asset} 
  />
  <source 
    media="(min-width: 769px)" 
    srcSet={ad.ad_desktop_asset} 
  />
  <img src={ad.ad_desktop_asset} alt={ad.ad_title} />
</picture>
```

**Fallback Handling:**
```tsx
<AdSlot 
  slotNumber="homepage-banner-1" 
  fallback={
    <div className="ad-placeholder">
      <p>Advertisement</p>
    </div>
  }
/>
```

---

### 2. Admin Dashboard

**Real-time Updates:**
```typescript
// Refresh stats periodically
const { data: stats } = useQuery({
  queryKey: ['ad-stats'],
  queryFn: () => adminAdApi.getStats().then(res => res.data),
  refetchInterval: 5 * 60 * 1000, // Every 5 minutes
});
```

**Optimistic Updates:**
```typescript
const updateMutation = useMutation({
  mutationFn: (data: UpdateAdvertisementPayload) => 
    adminAdApi.update(adId, data),
  onMutate: async (newData) => {
    // Cancel outgoing refetches
    await queryClient.cancelQueries({ queryKey: ['advertisement', adId] });
    
    // Snapshot previous value
    const previous = queryClient.getQueryData(['advertisement', adId]);
    
    // Optimistically update
    queryClient.setQueryData(['advertisement', adId], (old: any) => ({
      ...old,
      data: { ...old.data, ...newData },
    }));
    
    return { previous };
  },
  onError: (err, newData, context) => {
    // Rollback on error
    queryClient.setQueryData(['advertisement', adId], context?.previous);
  },
  onSettled: () => {
    queryClient.invalidateQueries({ queryKey: ['advertisement', adId] });
  },
});
```

**Batch Operations:**
```typescript
async function batchActivateAds(ids: number[], token: string) {
  const results = await Promise.allSettled(
    ids.map(id => adminAdApi.update(id, { status: 'active' }))
  );
  
  const succeeded = results.filter(r => r.status === 'fulfilled').length;
  const failed = results.filter(r => r.status === 'rejected').length;
  
  return { succeeded, failed };
}
```

---

### 3. Caching Strategy

**Query Key Structure:**
```typescript
// Public ads (client-side)
['active-ads']                    // All active ads
['active-ads', slotNumber]        // Specific slot

// Admin (dashboard)
['advertisements', filters]       // List with filters
['advertisement', id]             // Single ad
['ad-stats']                      // Dashboard stats
```

**Cache Times:**
```typescript
const CACHE_TIMES = {
  ACTIVE_ADS: 5 * 60 * 1000,      // 5 minutes
  AD_DETAILS: 2 * 60 * 1000,      // 2 minutes
  STATS: 10 * 60 * 1000,          // 10 minutes
};
```

**Invalidation:**
```typescript
// After create/update/delete
queryClient.invalidateQueries({ queryKey: ['advertisements'] });
queryClient.invalidateQueries({ queryKey: ['ad-stats'] });
queryClient.invalidateQueries({ queryKey: ['active-ads'] });
```

---

### 4. Performance Optimization

**Debounced Search:**
```tsx
import { useDebouncedValue } from '@/hooks/useDebouncedValue';

const [searchTerm, setSearchTerm] = useState('');
const debouncedSearch = useDebouncedValue(searchTerm, 500);

const { data } = useAdvertisements({
  ad_title: debouncedSearch,
  page: 1,
});
```

**Virtual Scrolling for Large Lists:**
```tsx
import { useVirtualizer } from '@tanstack/react-virtual';

const virtualizer = useVirtualizer({
  count: data?.meta.total || 0,
  getScrollElement: () => parentRef.current,
  estimateSize: () => 80,
});
```

**Prefetching:**
```typescript
// Prefetch next page
useEffect(() => {
  if (data?.meta.has_more) {
    queryClient.prefetchQuery({
      queryKey: ['advertisements', { ...filters, page: filters.page! + 1 }],
      queryFn: () => adminAdApi.list({ ...filters, page: filters.page! + 1 }),
    });
  }
}, [data, filters]);
```

---

## Testing

### Unit Tests (Frontend)

```typescript
// __tests__/api/advertisements.test.ts
import { describe, it, expect, vi } from 'vitest';
import { publicAdApi, adminAdApi } from '@/api/advertisements';

describe('Advertisement API', () => {
  describe('Public API', () => {
    it('should fetch active ads', async () => {
      const mockResponse: CollectionResponse<Advertisement> = {
        data: [
          {
            id: 1,
            ad_slot_number: 'test-slot',
            ad_title: 'Test Ad',
            // ... other fields
          },
        ],
        meta: { count: 1 },
      };
      
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: async () => mockResponse,
      });
      
      const result = await publicAdApi.getActive();
      expect(result.data).toHaveLength(1);
      expect(result.data[0].ad_slot_number).toBe('test-slot');
    });
    
    it('should track impression with session ID', async () => {
      global.fetch = vi.fn().mockResolvedValue({ ok: true, status: 204 });
      
      await publicAdApi.trackImpression(1, 'session-123');
      
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/advertisements/1/impression'),
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify({ session_id: 'session-123' }),
        })
      );
    });
  });
  
  describe('Admin API', () => {
    it('should create advertisement', async () => {
      const payload: CreateAdvertisementPayload = {
        ad_slot_number: 'new-slot',
        ad_title: 'New Ad',
        package_type: 'monthly',
        ad_published_date: '2025-01-01',
        status: 'draft',
        payment_status: 'pending',
        payment_amount: 500,
      };
      
      const mockResponse = {
        data: { id: 1, ...payload },
      };
      
      // Mock axios
      vi.spyOn(axios, 'post').mockResolvedValue({ data: mockResponse });
      
      const result = await adminAdApi.create(payload);
      expect(result.data.ad_slot_number).toBe('new-slot');
    });
  });
});
```

### Component Tests

```tsx
// __tests__/components/AdSlot.test.tsx
import { render, screen, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AdSlot } from '@/components/public/AdSlot';

const queryClient = new QueryClient();

describe('AdSlot', () => {
  it('should render ad when available', async () => {
    // Mock API
    vi.spyOn(publicAdApi, 'getActive').mockResolvedValue({
      data: [{
        id: 1,
        ad_slot_number: 'test-slot',
        ad_title: 'Test Ad',
        ad_excerpt: 'Test excerpt',
        ad_desktop_asset: 'https://example.com/ad.jpg',
        // ... other fields
      }],
      meta: { count: 1 },
    });
    
    render(
      <QueryClientProvider client={queryClient}>
        <AdSlot slotNumber="test-slot" />
      </QueryClientProvider>
    );
    
    await waitFor(() => {
      expect(screen.getByText('Test excerpt')).toBeInTheDocument();
    });
  });
  
  it('should render fallback when no ad available', async () => {
    vi.spyOn(publicAdApi, 'getActive').mockResolvedValue({
      data: [],
      meta: { count: 0 },
    });
    
    render(
      <QueryClientProvider client={queryClient}>
        <AdSlot 
          slotNumber="test-slot" 
          fallback={<div>No ads</div>}
        />
      </QueryClientProvider>
    );
    
    await waitFor(() => {
      expect(screen.getByText('No ads')).toBeInTheDocument();
    });
  });
});
```

---

## Status Flow Diagram

```
┌─────────┐
│  DRAFT  │ (Initial creation, payment pending)
└────┬────┘
     │
     ├─ (published_date in future) ──────────┐
     │                                        │
     ├─ (published_date ≤ today, paid) ──┐   │
     │                                    ▼   ▼
     │                             ┌──────────────┐
     │                             │  SCHEDULED   │
     │                             └──────┬───────┘
     │                                    │
     │                (Auto-activate job runs every 5min)
     │                                    │
     ▼                                    ▼
┌─────────┐                         ┌─────────┐
│ ACTIVE  │◄────────────────────────┤ ACTIVE  │
└────┬────┘                         └────┬────┘
     │                                    │
     ├─ (Manual pause) ─────► ┌────────┐ │
     │                         │ PAUSED │ │
     │                         └────┬───┘ │
     │                              │     │
     │◄───── (Manual resume) ───────┘     │
     │                                    │
     │            (ending_date passes, auto-expire job)
     │                                    │
     ▼                                    ▼
┌─────────┐                         ┌─────────┐
│ EXPIRED │◄────────────────────────┤ EXPIRED │
└─────────┘                         └─────────┘
```

---

## Quick Reference Card

### Endpoints Summary

| Endpoint | Method | Auth | Purpose |
|----------|--------|------|---------|
| `/advertisements/active` | GET | No | Fetch active ads for display |
| `/advertisements/{id}/impression` | POST | No | Track impression |
| `/advertisements/{id}/click` | POST | No | Track click |
| `/admin/advertisements` | GET | Yes | List/search (paginated) |
| `/admin/advertisements` | POST | Yes | Create ad |
| `/admin/advertisements/{id}` | GET | Yes | Get details |
| `/admin/advertisements/{id}` | PATCH | Yes | Update ad |
| `/admin/advertisements/{id}` | DELETE | Yes | Delete ad |
| `/admin/advertisements/{id}/restore` | POST | Yes | Restore ad |
| `/admin/advertisements/stats` | GET | Yes | Dashboard stats |

### Status Values
- `draft` - Being prepared
- `scheduled` - Will activate on publish date
- `active` - Currently live
- `paused` - Temporarily disabled
- `expired` - Past ending date

### Package Types
- `weekly` - 7 days
- `monthly` - 30 days
- `yearly` - 365 days

### Payment Statuses
- `pending` - Awaiting payment
- `paid` - Payment received
- `refunded` - Payment returned
- `failed` - Payment failed

---

## Example: Complete Admin Dashboard Page

```tsx
// pages/admin/advertisements/index.tsx
import React, { useState } from 'react';
import { AdStatsDashboard } from '@/components/admin/AdStatsDashboard';
import { AdvertisementTable } from '@/components/admin/AdvertisementTable';
import { Link } from 'react-router-dom';

export const AdvertisementsPage: React.FC = () => {
  const [view, setView] = useState<'active' | 'all' | 'expired'>('all');
  
  const filtersByView = {
    active: { status: 'active' as AdStatus },
    all: {},
    expired: { status: 'expired' as AdStatus },
  };
  
  return (
    <div className="container mx-auto p-6">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Advertisement CRM</h1>
        
        <Link 
          to="/admin/advertisements/create"
          className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          + Create Advertisement
        </Link>
      </div>
      
      {/* Statistics */}
      <AdStatsDashboard />
      
      {/* View Tabs */}
      <div className="flex gap-2 my-6 border-b">
        <button
          onClick={() => setView('all')}
          className={`px-4 py-2 ${view === 'all' ? 'border-b-2 border-blue-600 font-semibold' : ''}`}
        >
          All Advertisements
        </button>
        <button
          onClick={() => setView('active')}
          className={`px-4 py-2 ${view === 'active' ? 'border-b-2 border-blue-600 font-semibold' : ''}`}
        >
          Active
        </button>
        <button
          onClick={() => setView('expired')}
          className={`px-4 py-2 ${view === 'expired' ? 'border-b-2 border-blue-600 font-semibold' : ''}`}
        >
          Expired
        </button>
      </div>
      
      {/* Table with Filters */}
      <AdvertisementTable initialFilters={filtersByView[view]} />
    </div>
  );
};
```

---

## Example: Complete Client Ad Integration

```tsx
// pages/Home.tsx
import { AdSlot } from '@/components/public/AdSlot';
import { AD_SLOTS } from '@/hooks/useAdSlots';

export const HomePage: React.FC = () => {
  return (
    <div className="homepage">
      {/* Hero Section */}
      <section className="hero">
        <h1>Welcome to Passport.et</h1>
        
        {/* Banner Ad */}
        <AdSlot 
          slotNumber={AD_SLOTS.HOMEPAGE_BANNER}
          className="my-8 max-w-5xl mx-auto"
          fallback={<div className="h-48 bg-gray-100 rounded" />}
        />
      </section>
      
      {/* Main Content */}
      <div className="grid grid-cols-12 gap-6">
        <main className="col-span-8">
          <h2>Latest Content</h2>
          {/* Your content */}
        </main>
        
        <aside className="col-span-4">
          {/* Sidebar Ad (Sticky) */}
          <div className="sticky top-4">
            <AdSlot 
              slotNumber={AD_SLOTS.SIDEBAR_STICKY}
              className="mb-4"
            />
          </div>
        </aside>
      </div>
      
      {/* Bottom Ad */}
      <AdSlot 
        slotNumber={AD_SLOTS.HOMEPAGE_BOTTOM}
        className="my-8"
      />
    </div>
  );
};
```

---

## Automated Tasks (Backend)

These run automatically - frontend doesn't need to implement but should be aware:

### 1. Expiry Notifications
- **Schedule:** Daily at 09:00 AM
- **Trigger:** 3 days before `ad_ending_date`
- **Action:** Telegram notification sent to admin

### 2. Auto-Expire
- **Schedule:** Daily at 00:15 AM
- **Trigger:** `ad_ending_date` < today AND status = active/scheduled
- **Action:** Status changed to `expired`

### 3. Auto-Activate
- **Schedule:** Every 5 minutes
- **Trigger:** status = scheduled AND `ad_published_date` ≤ today AND payment = paid
- **Action:** Status changed to `active`

**Frontend Impact:** Ads automatically transition between states. Dashboard should reflect these changes after cache expires (5-10 minutes) or manual refresh.

---

## Support & Troubleshooting

### Common Issues

**Issue: Ad not showing on frontend**
- Check status is `active`
- Verify `ad_published_date` ≤ today
- Verify `ad_ending_date` ≥ today or null
- Check `ad_slot_number` matches frontend component
- Clear cache: `Cache::tags(['ad_crm'])->flush()`

**Issue: Cannot activate advertisement**
- Ensure `payment_status` is `paid`
- Check `ad_published_date` is not in future
- If future date, status will be `scheduled` (auto-activates later)

**Issue: File upload fails**
- Max file size: 10MB
- Allowed formats: jpg, jpeg, png, gif, svg, mp4, webp
- Use `multipart/form-data` content type

**Issue: Tracking not working**
- Verify session ID is being generated/stored
- Check rate limits (240 req/min)
- Ensure jobs are being processed (check Horizon dashboard)

---

## Changelog & Versioning

**Version:** 1.0.0 (2025-01-15)

**API Version:** v1

**Breaking Changes:** None (initial release)

**Planned Features:**
- A/B testing (multiple ads per slot)
- Geo-targeting
- Advanced analytics dashboard
- Client portal (self-service)

---

## Additional Resources

- **Backend API Docs:** `docs/advertisement-crm-api.md`
- **Implementation Summary:** `docs/advertisement-crm-implementation-summary.md`
- **AGENTS.md:** Architecture guidelines and best practices
- **Horizon Dashboard:** `https://yourdomain.com/horizon` (monitor queue jobs)
- **Laravel Pulse:** `https://yourdomain.com/pulse` (performance metrics)

---

**Questions or Issues?** Contact the development team or submit a ticket.
